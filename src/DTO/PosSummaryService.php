<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Carries aggregated POS data between the repository and the HTTP layer.
 */
final readonly class PosSummaryDTO implements \JsonSerializable
{
    public function __construct(
        public int $id,
        public string $name,
        public int $orderCount,
        public float $totalRevenue,
        public float $averageOrderValue,
    ) {}

    /** @return array<string, int|float|string> */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'orderCount' => $this->orderCount,
            'totalRevenue' => round($this->totalRevenue, 2),
            'averageOrderValue' => round($this->averageOrderValue, 2),
        ];
    }

}
