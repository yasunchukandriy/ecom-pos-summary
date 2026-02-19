<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\DateRangeDTO;
use App\DTO\PosSummaryDTO;
use App\Repository\PosSummaryRepositoryInterface;

/**
 * Orchestrates POS summary retrieval.
 */
final readonly class PosSummaryService
{
    public function __construct(
        private PosSummaryRepositoryInterface $repository,
    ) {
    }

    /** @return PosSummaryDTO[] */
    public function getSummaries(DateRangeDTO $dateRange): array
    {
        return $this->repository->findSummariesByDateRange($dateRange);
    }
}
