<?php

declare(strict_types=1);

namespace App\Utils;

class GradeCalculator
{
    public static function calculateGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 90.0 => 'A+',
            $percentage >= 80.0 => 'A',
            $percentage >= 70.0 => 'B',
            $percentage >= 60.0 => 'C',
            $percentage >= 50.0 => 'D',
            default => 'F',
        };
    }

    public static function badgeClass(?string $grade): string
    {
        return match ($grade) {
            'A+', 'A' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
            'B' => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
            'C' => 'bg-yellow-500/20 text-yellow-300 border-yellow-500/40',
            'D' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
            default => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
        };
    }

    public static function calculate(float $marksObtained, float $maxMarks = 100.0, float $passMarks = 40.0): array
    {
        if ($maxMarks <= 0) {
            $maxMarks = 100.0;
        }

        $percentage = round(($marksObtained / $maxMarks) * 100, 2);
        $grade = self::calculateGrade($percentage);
        $isPass = $marksObtained >= $passMarks;
        $badgeClass = self::badgeClass($grade);

        return [
            'marks_obtained' => $marksObtained,
            'max_marks' => $maxMarks,
            'percentage' => $percentage,
            'grade' => $grade,
            'is_pass' => $isPass,
            'is_passed' => $isPass,
            'badge_class' => $badgeClass,
        ];
    }
}
