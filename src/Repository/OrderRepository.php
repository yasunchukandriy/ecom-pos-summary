<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\DateRangeDTO;
use App\DTO\PosSummaryDTO;
use App\Entity\Order;
use App\Entity\PointOfSale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Handles aggregation queries for POS summary data.
 *
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository implements PosSummaryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /** @return PosSummaryDTO[] */
    public function findSummariesByDateRange(DateRangeDTO $dateRange): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $results = $qb
            ->select(
                'pos.id',
                'pos.name',
            )
            ->from(PointOfSale::class, 'pos')
            ->where('pos.isActive = true')
            ->groupBy('pos.id, pos.name')
            ->orderBy('pos.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row) => new PosSummaryDTO(
                id: (int) $row['id'],
                name: $row['name'],
                orderCount: 0,
                totalRevenue: 0.0,
                averageOrderValue: 0.0,
            ),
            $results
        );
    }
}
