<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\DateRangeDTO;
use App\DTO\PosSummaryDTO;

/**
 * Abstraction over POS summary data retrieval.
 */
interface PosSummaryRepositoryInterface
{

    /** @return PosSummaryDTO[] */
    public function findSummariesByDateRange(DateRangeDTO $dateRange): array;

}
