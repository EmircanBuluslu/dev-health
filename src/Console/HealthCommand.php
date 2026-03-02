<?php

namespace DevHealth\LaravelHealth\Console;

use DevHealth\LaravelHealth\Services\DoctorRunner;
use DevHealth\LaravelHealth\Services\RiskCalculator;
use DevHealth\LaravelHealth\Services\ReportFormatter;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class HealthCommand extends Command
{
    protected $signature = 'dev:health 
                            {--format=cli : Çıktı formatı (cli, json, html)}
                            {--output= : HTML/JSON çıktısı için dosya yolu}';

    protected $description = 'Laravel projesinin sağlık kontrolünü yapar';

    public function handle(
        DoctorRunner $runner,
        RiskCalculator $calculator,
        ReportFormatter $formatter
    ): int {
        $this->info('🏥 Dev:Health başlatılıyor...');
        $this->newLine();

        // Kontrolleri çalıştır
        $results = $runner->run();
        
        // Risk hesapla
        $risk = $calculator->calculate($results);

        // Format seçimi
        $format = $this->option('format');
        $output = $this->option('output');

        match($format) {
            'json' => $this->outputJson($results, $risk, $formatter, $output),
            'html' => $this->outputHtml($results, $risk, $formatter, $output),
            default => $formatter->formatCli($results, $risk, $this->output),
        };

        // Exit code: fail varsa 1, yoksa 0
        return $risk['stats']['fail'] > 0 ? 1 : 0;
    }

    private function outputJson(array $results, array $risk, ReportFormatter $formatter, ?string $output): void
    {
        $json = $formatter->formatJson($results, $risk);

        if ($output) {
            file_put_contents($output, $json);
            $this->info("✓ JSON raporu kaydedildi: {$output}");
        } else {
            $this->line($json);
        }
    }

    private function outputHtml(array $results, array $risk, ReportFormatter $formatter, ?string $output): void
    {
        $html = $formatter->formatHtml($results, $risk);

        if (!$output) {
            $output = 'health-report.html';
        }

        file_put_contents($output, $html);
        $this->info("✓ HTML raporu kaydedildi: {$output}");
    }
}
