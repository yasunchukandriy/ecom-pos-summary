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
                'COUNT(o.id) AS orderCount',
                'COALESCE(SUM(o.totalAmount), 0) AS totalRevenue',
                'COALESCE(AVG(o.totalAmount), 0) AS averageOrderValue'
            )
            ->from(PointOfSale::class, 'pos')
            ->leftJoin(
                'pos.orders',
                'o',
                'WITH',
                'o.createdAt >= :from AND o.createdAt <= :to'
            )
            ->where('pos.isActive = true')
            ->groupBy('pos.id, pos.name')
            ->orderBy('pos.name', 'ASC')
            ->setParameter('from', $dateRange->from)
            ->setParameter('to', $dateRange->to)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row) => new PosSummaryDTO(
                id: (int) $row['id'],
                name: $row['name'],
                orderCount: (int) $row['orderCount'],
                totalRevenue: (float) $row['totalRevenue'],
                averageOrderValue: (float) $row['averageOrderValue'],
            ),
            $results
        );
    }
}
