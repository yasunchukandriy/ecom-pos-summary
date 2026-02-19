<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Lightweight probe for load balancers and container orchestration.
 */
#[AsController]
final class HealthController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    #[Route('/api/health', name: 'api_health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        try {
            $this->connection->executeQuery('SELECT 1');
            $dbStatus = 'ok';
        } catch (\Throwable) {
            $dbStatus = 'error';
        }

        $status = 'ok' === $dbStatus ? 'healthy' : 'unhealthy';
        $httpCode = 'ok' === $dbStatus ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return $this->json([
            'status' => $status,
            'checks' => [
                'database' => $dbStatus,
            ],
        ], $httpCode);
    }
}
