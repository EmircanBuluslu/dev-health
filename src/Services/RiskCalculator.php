<?php

namespace DevHealth\LaravelHealth\Services;

use DevHealth\LaravelHealth\Enums\Status;
use DevHealth\LaravelHealth\ValueObjects\Result;

class RiskCalculator
{
    public function calculate(array $results): array
    {
        $stats = [
            'ok' => 0,
            'warning' => 0,
            'fail' => 0,
            'total' => 0,
        ];

        foreach ($results as $checkResults) {
            foreach ($checkResults['results'] as $result) {
                /** @var Result $result */
                $stats['total']++;
                
                match($result->status) {
                    Status::OK => $stats['ok']++,
                    Status::WARNING => $stats['warning']++,
                    Status::FAIL => $stats['fail']++,
                };
            }
        }

        $issueCount = $stats['warning'] + $stats['fail'];
        $grade = $this->calculateGrade($issueCount, $stats['fail']);
        $score = $this->calculateScore($stats);

        return [
            'stats' => $stats,
            'grade' => $grade,
            'score' => $score,
            'issue_count' => $issueCount,
        ];
    }

    private function calculateGrade(int $issueCount, int $failCount): string
    {
        // Kritik hatalar varsa direkt düşük not
        if ($failCount >= 5) {
            return 'D';
        }

        if ($failCount >= 3) {
            return 'C';
        }

        // Toplam sorun sayısına göre
        if ($issueCount === 0) {
            return 'A';
        }

        if ($issueCount <= 2) {
            return 'A';
        }

        if ($issueCount <= 5) {
            return 'B';
        }

        if ($issueCount <= 10) {
            return 'C';
        }

        return 'D';
    }

    private function calculateScore(array $stats): int
    {
        $total = $stats['total'];
        
        if ($total === 0) {
            return 100;
        }

        $score = 100;
        $score -= ($stats['warning'] * 5);
        $score -= ($stats['fail'] * 15);

        return max(0, $score);
    }

    public function getGradeColor(string $grade): string
    {
        return match($grade) {
            'A' => 'green',
            'B' => 'cyan',
            'C' => 'yellow',
            'D' => 'red',
            default => 'white',
        };
    }

    public function getGradeDescription(string $grade): string
    {
        return match($grade) {
            'A' => 'Mükemmel - Projeniz harika durumda!',
            'B' => 'İyi - Birkaç küçük iyileştirme yapılabilir',
            'C' => 'Dikkat - Bazı sorunlar düzeltilmeli',
            'D' => 'Kritik - Acil müdahale gerekiyor!',
            default => 'Bilinmeyen',
        };
    }
}
