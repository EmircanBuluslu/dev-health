<?php

namespace DevHealth\LaravelHealth\Services;

use DevHealth\LaravelHealth\ValueObjects\Result;
use Symfony\Component\Console\Output\OutputInterface;

class ReportFormatter
{
    public function __construct(
        private RiskCalculator $calculator
    ) {}

    public function formatCli(array $results, array $risk, OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<fg=cyan>🏥 Dev:Health - Laravel Sağlık Raporu</>');
        $output->writeln('');

        foreach ($results as $checkName => $checkData) {
            $output->writeln("<fg=yellow>═══ {$checkName} ═══</>");
            $output->writeln("<fg=gray>{$checkData['description']}</>");
            $output->writeln('');

            foreach ($checkData['results'] as $result) {
                /** @var Result $result */
                $this->formatResult($result, $output);
            }

            $output->writeln('');
        }

        $this->formatSummary($risk, $output);
    }

    private function formatResult(Result $result, OutputInterface $output): void
    {
        $icon = $result->status->getIcon();
        $color = $result->status->getColor();
        
        $output->writeln("<fg={$color}>{$icon} {$result->message}</>");

        if ($result->file) {
            $location = "   📁 {$result->file}";
            if ($result->line) {
                $location .= ":{$result->line}";
            }
            $output->writeln("<fg=gray>{$location}</>");
        }

        if ($result->suggestion) {
            $output->writeln("<fg=cyan>   💡 {$result->suggestion}</>");
        }

        if (!empty($result->metadata)) {
            foreach ($result->metadata as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $output->writeln("<fg=gray>   • {$key}: {$value}</>");
            }
        }
    }

    private function formatSummary(array $risk, OutputInterface $output): void
    {
        $output->writeln('<fg=yellow>═══ 📊 Özet ═══</>');
        $output->writeln('');

        $stats = $risk['stats'];
        $grade = $risk['grade'];
        $score = $risk['score'];
        $gradeColor = $this->calculator->getGradeColor($grade);
        $gradeDesc = $this->calculator->getGradeDescription($grade);

        $output->writeln([
            "Toplam Kontrol: <fg=white>{$stats['total']}</>",
            "✓ Başarılı: <fg=green>{$stats['ok']}</>",
            "⚠ Uyarı: <fg=yellow>{$stats['warning']}</>",
            "✗ Hata: <fg=red>{$stats['fail']}</>",
            "",
            "Skor: <fg=cyan>{$score}/100</>",
            "Not: <fg={$gradeColor}>{$grade}</>",
            "<fg={$gradeColor}>{$gradeDesc}</>",
        ]);
    }

    public function formatJson(array $results, array $risk): string
    {
        $data = [
            'timestamp' => now()->toIso8601String(),
            'risk' => $risk,
            'checks' => [],
        ];

        foreach ($results as $checkName => $checkData) {
            $data['checks'][$checkName] = [
                'description' => $checkData['description'],
                'results' => array_map(fn($r) => $r->toArray(), $checkData['results']),
            ];
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function formatHtml(array $results, array $risk): string
    {
        $stats = $risk['stats'];
        $grade = $risk['grade'];
        $score = $risk['score'];
        $gradeColor = $this->getHtmlColor($grade);
        $gradeDesc = $this->calculator->getGradeDescription($grade);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev:Health Raporu</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        .header h1 { margin: 0 0 10px 0; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; }
        .stat-card .value { font-size: 32px; font-weight: bold; }
        .grade { background: {$gradeColor}; color: white; padding: 40px; border-radius: 10px; text-align: center; margin-bottom: 30px; }
        .grade h2 { margin: 0; font-size: 48px; }
        .check { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .check h3 { margin: 0 0 10px 0; color: #333; }
        .result { padding: 15px; margin: 10px 0; border-left: 4px solid #ddd; background: #f9f9f9; border-radius: 4px; }
        .result.ok { border-color: #10b981; }
        .result.warning { border-color: #f59e0b; }
        .result.fail { border-color: #ef4444; }
        .result .message { font-weight: 500; margin-bottom: 8px; }
        .result .file { color: #666; font-size: 14px; font-family: monospace; }
        .result .suggestion { color: #0ea5e9; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 Dev:Health - Laravel Sağlık Raporu</h1>
        <p>Oluşturulma: {$this->formatDate()}</p>
    </div>

    <div class="grade">
        <h2>Not: {$grade}</h2>
        <p>Skor: {$score}/100</p>
        <p>{$gradeDesc}</p>
    </div>

    <div class="summary">
        <div class="stat-card">
            <h3>Toplam Kontrol</h3>
            <div class="value">{$stats['total']}</div>
        </div>
        <div class="stat-card">
            <h3>✓ Başarılı</h3>
            <div class="value" style="color: #10b981;">{$stats['ok']}</div>
        </div>
        <div class="stat-card">
            <h3>⚠ Uyarı</h3>
            <div class="value" style="color: #f59e0b;">{$stats['warning']}</div>
        </div>
        <div class="stat-card">
            <h3>✗ Hata</h3>
            <div class="value" style="color: #ef4444;">{$stats['fail']}</div>
        </div>
    </div>

HTML;

        foreach ($results as $checkName => $checkData) {
            $html .= "<div class='check'>\n";
            $html .= "<h3>{$checkName}</h3>\n";
            $html .= "<p style='color: #666;'>{$checkData['description']}</p>\n";

            foreach ($checkData['results'] as $result) {
                /** @var Result $result */
                $statusClass = strtolower($result->status->value);
                $html .= "<div class='result {$statusClass}'>\n";
                $html .= "<div class='message'>{$result->status->getIcon()} {$result->message}</div>\n";
                
                if ($result->file) {
                    $location = $result->file . ($result->line ? ":{$result->line}" : '');
                    $html .= "<div class='file'>📁 {$location}</div>\n";
                }
                
                if ($result->suggestion) {
                    $html .= "<div class='suggestion'>💡 {$result->suggestion}</div>\n";
                }
                
                $html .= "</div>\n";
            }

            $html .= "</div>\n";
        }

        $html .= "</body>\n</html>";

        return $html;
    }

    private function getHtmlColor(string $grade): string
    {
        return match($grade) {
            'A' => '#10b981',
            'B' => '#06b6d4',
            'C' => '#f59e0b',
            'D' => '#ef4444',
            default => '#6b7280',
        };
    }

    private function formatDate(): string
    {
        return now()->locale('tr')->isoFormat('D MMMM YYYY, HH:mm');
    }
}
