<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\DateRangeDTO;
use App\Service\PosSummaryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class PosSummaryController extends AbstractController
{
    public function __construct(
        private readonly PosSummaryService $service,
    ) {
    }

    #[Route('/api/pos/summary', name: 'api_pos_summary', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $dateRange = DateRangeDTO::fromQueryParams(
                $request->query->get('from'),
                $request->query->get('to'),
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $summaries = $this->service->getSummaries($dateRange);

        return $this->json([
            'meta' => [
                'period' => [
                    'from' => $dateRange->from->format('Y-m-d'),
                    'to' => $dateRange->to->format('Y-m-d'),
                ],
                'count' => count($summaries),
                'generatedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ],
            'data' => $summaries,
        ]);
    }
}
