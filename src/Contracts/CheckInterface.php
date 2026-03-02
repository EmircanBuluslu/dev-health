<?php

namespace DevHealth\LaravelHealth\Contracts;

use DevHealth\LaravelHealth\ValueObjects\Result;

interface CheckInterface
{
    /**
     * Kontrolü çalıştır ve sonuç döndür
     *
     * @return Result|Result[]
     */
    public function run(): Result|array;

    /**
     * Kontrol adını döndür
     */
    public function getName(): string;

    /**
     * Kontrol açıklamasını döndür
     */
    public function getDescription(): string;
}
