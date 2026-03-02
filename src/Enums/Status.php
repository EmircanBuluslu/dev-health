<?php

namespace DevHealth\LaravelHealth\Enums;

enum Status: string
{
    case OK = 'OK';
    case WARNING = 'WARNING';
    case FAIL = 'FAIL';

    public function getColor(): string
    {
        return match($this) {
            self::OK => 'green',
            self::WARNING => 'yellow',
            self::FAIL => 'red',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::OK => '✓',
            self::WARNING => '⚠',
            self::FAIL => '✗',
        };
    }

    public function getSeverity(): int
    {
        return match($this) {
            self::OK => 0,
            self::WARNING => 1,
            self::FAIL => 2,
        };
    }
}
