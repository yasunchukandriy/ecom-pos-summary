<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\DateRangeDTO;
use App\DTO\PosSummaryDTO;
use App\Repository\PosSummaryRepositoryInterface;
use App\Service\PosSummaryService;
use PHPUnit\Framework\TestCase;

final class PosSummaryServiceTest extends TestCase
{
    public function testGetSummariesDelegatesToRepository(): void
    {
        $dateRange = new DateRangeDTO(
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-01-31'),
        );

        $expected = [
            new PosSummaryDTO(1, 'Store A', 10, 2000.50, 100.05),
            new PosSummaryDTO(2, 'Store B', 5, 250.00, 50.00),
        ];

        $repository = $this->createMock(PosSummaryRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findSummariesByDateRange')
            ->with($dateRange)
            ->willReturn($expected);

        $service = new PosSummaryService($repository);
        $result = $service->getSummaries($dateRange);

        $this->assertSame($expected, $result);
    }

    public function testGetSummariesReturnsEmptyArrayWhenNoData(): void
    {
        $dateRange = new DateRangeDTO(
            new \DateTimeImmutable('2020-01-01'),
            new \DateTimeImmutable('2020-01-31'),
        );

        $repository = $this->createMock(PosSummaryRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findSummariesByDateRange')
            ->with($dateRange)
            ->willReturn([]);

        $service = new PosSummaryService($repository);
        $result = $service->getSummaries($dateRange);

        $this->assertSame([], $result);
    }

    public function testGetSummariesPreservesDtoValues(): void
    {
        $dateRange = DateRangeDTO::currentMonth();

        $dto = new PosSummaryDTO(
            id: 42,
            name: 'Test POS',
            orderCount: 100,
            totalRevenue: 9999.99,
            averageOrderValue: 99.9999,
        );

        $repository = $this->createMock(PosSummaryRepositoryInterface::class);
        $repository
            ->method('findSummariesByDateRange')
            ->willReturn([$dto]);

        $service = new PosSummaryService($repository);
        $result = $service->getSummaries($dateRange);

        $this->assertCount(1, $result);
        $this->assertSame(42, $result[0]->id);
        $this->assertSame('Test POS', $result[0]->name);
        $this->assertSame(100, $result[0]->orderCount);
        $this->assertSame(9999.99, $result[0]->totalRevenue);
    }
}
