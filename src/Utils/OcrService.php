<?php

declare(strict_types=1);

namespace App\Utils;

class OcrService
{
    /**
     * Check if tesseract binary is available on the system.
     */
    public static function isAvailable(): bool
    {
        $output = [];
        $returnVar = 0;
        @exec('which tesseract 2>/dev/null', $output, $returnVar);
        return $returnVar === 0 && !empty($output);
    }

    /**
     * Resolve magick or convert binary if available
     */
    public static function getMagickBinary(): ?string
    {
        $bin = trim((string)@shell_exec('which magick 2>/dev/null || which convert 2>/dev/null'));
        return (!empty($bin) && file_exists($bin)) ? $bin : null;
    }

    /**
     * Extract text from an image file path (PNG, JPG, WEBP, TIFF, or PDF).
     */
    public static function extractText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        if (!self::isAvailable()) {
            throw new \RuntimeException('OCR Engine (tesseract) is not available on this server.');
        }

        $tempDir = sys_get_temp_dir();
        $uniqueId = uniqid('ocr_', true);
        $preprocessedImage = $tempDir . '/' . $uniqueId . '_prep.png';

        try {
            // Determine file mime / extension
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath) ?: '';
            finfo_close($finfo);

            $isPdf = ($mime === 'application/pdf') || str_ends_with(strtolower($filePath), '.pdf');

            $magickBin = self::getMagickBinary();

            if ($magickBin) {
                // Constrain ImageMagick memory usage to avoid RAM spikes on low-RAM servers
                $magickLimits = '-limit memory 64MB -limit map 128MB -limit disk 500MB';
                if ($isPdf) {
                    // Extract first page of PDF at high density
                    $cmd = sprintf('%s %s -density 250 %s[0] -colorspace Gray -resize 150%% %s 2>&1',
                        escapeshellcmd($magickBin),
                        $magickLimits,
                        escapeshellarg($filePath),
                        escapeshellarg($preprocessedImage)
                    );
                } else {
                    // Preprocess image for maximum OCR accuracy (grayscale)
                    $cmd = sprintf('%s %s %s -colorspace Gray %s 2>&1',
                        escapeshellcmd($magickBin),
                        $magickLimits,
                        escapeshellarg($filePath),
                        escapeshellarg($preprocessedImage)
                    );
                }
                @exec($cmd);
            }

            // Fallback to original image if preprocessing failed or convert was unavailable
            $targetImage = (file_exists($preprocessedImage) && filesize($preprocessedImage) > 50)
                ? $preprocessedImage
                : $filePath;

            // Execute Tesseract: Try PSM 3 (standard automatic page segmentation for tables & rosters)
            $tesseractCmd = sprintf('tesseract %s stdout -l eng --psm 3 2>/dev/null', escapeshellarg($targetImage));
            $text = (string) shell_exec($tesseractCmd);

            // If PSM 3 returned very little text, try PSM 6 (single uniform block) or PSM 4 (single column variable sizes)
            if (strlen(trim($text)) < 25) {
                $altText6 = (string) shell_exec(sprintf('tesseract %s stdout -l eng --psm 6 2>/dev/null', escapeshellarg($targetImage)));
                if (strlen(trim($altText6)) > strlen(trim($text))) {
                    $text = $altText6;
                }
            }

            if (strlen(trim($text)) < 25) {
                $altText4 = (string) shell_exec(sprintf('tesseract %s stdout -l eng --psm 4 2>/dev/null', escapeshellarg($targetImage)));
                if (strlen(trim($altText4)) > strlen(trim($text))) {
                    $text = $altText4;
                }
            }

            if (strlen(trim($text)) < 25) {
                $altText11 = (string) shell_exec(sprintf('tesseract %s stdout -l eng --psm 11 2>/dev/null', escapeshellarg($targetImage)));
                if (strlen(trim($altText11)) > strlen(trim($text))) {
                    $text = $altText11;
                }
            }

            // If preprocessed image produced very little text, try directly on raw original file
            if ($targetImage !== $filePath && strlen(trim($text)) < 25) {
                $rawText = (string) shell_exec(sprintf('tesseract %s stdout -l eng --psm 3 2>/dev/null', escapeshellarg($filePath)));
                if (strlen(trim($rawText)) > strlen(trim($text))) {
                    $text = $rawText;
                }
            }

            return trim($text);
        } finally {
            if (file_exists($preprocessedImage)) {
                @unlink($preprocessedImage);
            }
        }
    }

    /**
     * Extract text from Base64 data (e.g. from canvas or clipboard screenshot).
     */
    public static function extractFromBase64(string $base64Data): string
    {
        // Strip data URI header if present (e.g. data:image/png;base64,...)
        if (str_contains($base64Data, ';base64,')) {
            $base64Data = substr($base64Data, strpos($base64Data, ';base64,') + 8);
        } elseif (preg_match('/^data:image\/[^;]+;base64,/', $base64Data)) {
            $base64Data = preg_replace('/^data:image\/[^;]+;base64,/', '', $base64Data);
        }

        // Clean any spaces or newlines introduced by POST transport
        $base64Data = str_replace([' ', "\r", "\n", "\t"], ['+', '', '', ''], $base64Data);

        $decoded = base64_decode($base64Data, true);
        if ($decoded === false || empty($decoded)) {
            return '';
        }

        $tempFile = sys_get_temp_dir() . '/' . uniqid('ocr_b64_', true) . '.png';
        try {
            file_put_contents($tempFile, $decoded);
            return self::extractText($tempFile);
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }
}
