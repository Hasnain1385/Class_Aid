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

            // Image preprocessing using ImageMagick if available
            $convertCmd = 'which convert 2>/dev/null';
            $hasConvert = !empty(shell_exec($convertCmd));

            if ($hasConvert) {
                if ($isPdf) {
                    // Extract first page of PDF at high density
                    $cmd = sprintf('convert -density 250 %s[0] -colorspace Gray -contrast-stretch 0 -resize 150%% %s 2>&1',
                        escapeshellarg($filePath),
                        escapeshellarg($preprocessedImage)
                    );
                } else {
                    // Preprocess image for maximum OCR accuracy (grayscale, contrast-stretch, moderate sharpen)
                    $cmd = sprintf('convert %s -colorspace Gray -contrast-stretch 0 %s 2>&1',
                        escapeshellarg($filePath),
                        escapeshellarg($preprocessedImage)
                    );
                }
                @exec($cmd);
            }

            // Fallback to original image if preprocessing failed or convert was unavailable
            $targetImage = (file_exists($preprocessedImage) && filesize($preprocessedImage) > 0)
                ? $preprocessedImage
                : $filePath;

            // Execute Tesseract. Try PSM 6 (single uniform block) first
            $tesseractCmd = sprintf('tesseract %s stdout --psm 6 2>/dev/null', escapeshellarg($targetImage));
            $text = (string) shell_exec($tesseractCmd);

            // If PSM 6 returned very little text, try PSM 4 (single column variable sizes) or PSM 3 (fully automatic)
            if (strlen(trim($text)) < 15) {
                $tesseractCmdAlt = sprintf('tesseract %s stdout --psm 4 2>/dev/null', escapeshellarg($targetImage));
                $altText = (string) shell_exec($tesseractCmdAlt);
                if (strlen(trim($altText)) > strlen(trim($text))) {
                    $text = $altText;
                }
            }

            if (strlen(trim($text)) < 15) {
                $tesseractCmdAlt3 = sprintf('tesseract %s stdout --psm 3 2>/dev/null', escapeshellarg($targetImage));
                $altText3 = (string) shell_exec($tesseractCmdAlt3);
                if (strlen(trim($altText3)) > strlen(trim($text))) {
                    $text = $altText3;
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
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        }

        $decoded = base64_decode($base64Data);
        if ($decoded === false) {
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
