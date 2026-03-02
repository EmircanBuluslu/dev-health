<?php

namespace DevHealth\LaravelHealth\ValueObjects;

use DevHealth\LaravelHealth\Enums\Status;

class Result
{
    public function __construct(
        public readonly Status $status,
        public readonly string $message,
        public readonly ?string $file = null,
        public readonly ?int $line = null,
        public readonly ?string $suggestion = null,
        public readonly array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'suggestion' => $this->suggestion,
            'metadata' => $this->metadata,
        ];
    }

    public static function ok(string $message, ?string $suggestion = null): self
    {
        return new self(Status::OK, $message, suggestion: $suggestion);
    }

    public static function warning(
        string $message,
        ?string $file = null,
        ?int $line = null,
        ?string $suggestion = null,
        array $metadata = []
    ): self {
        return new self(Status::WARNING, $message, $file, $line, $suggestion, $metadata);
    }

    public static function fail(
        string $message,
        ?string $file = null,
        ?int $line = null,
        ?string $suggestion = null,
        array $metadata = []
    ): self {
        return new self(Status::FAIL, $message, $file, $line, $suggestion, $metadata);
    }

    public static function info(
        string $message,
        ?string $file = null,
        ?int $line = null,
        ?string $suggestion = null,
        array $metadata = []
    ): self {
        return new self(Status::OK, $message, $file, $line, $suggestion, $metadata);
    }
}
