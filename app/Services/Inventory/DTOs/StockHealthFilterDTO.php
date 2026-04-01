<?php

namespace App\Services\Inventory\DTOs;

readonly class StockHealthFilterDTO
{
    public function __construct(
        public readonly string $search       = '',
        public readonly string $processOwner = '',
        public readonly string $family       = '',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            search:       trim($data['search']        ?? ''),
            processOwner: trim($data['process_owner'] ?? ''),
            family:       trim($data['family']        ?? ''),
        );
    }
}
