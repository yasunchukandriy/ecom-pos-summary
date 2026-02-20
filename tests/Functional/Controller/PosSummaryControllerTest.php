<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Order;
use App\Entity\PointOfSale;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PosSummaryControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->resetDatabase();
        $this->loadTestData();
    }

    public function testReturnsSuccessWithDateFilters(): void
    {
        $this->client->request('GET', '/api/pos/summary', [
            'from' => '2025-01-01',
            'to' => '2025-01-31',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $body = $this->decodeResponse();
        $this->assertArrayHasKey('meta', $body);
        $this->assertArrayHasKey('data', $body);
    }

    public function testReturnsSuccessWithoutParameters(): void
    {
        $this->client->request('GET', '/api/pos/summary');

        $this->assertResponseIsSuccessful();

        $body = $this->decodeResponse();
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);
    }

    public function testMetadataContainsPeriodAndCount(): void
    {
        $this->client->request('GET', '/api/pos/summary', [
            'from' => '2025-01-01',
            'to' => '2025-12-31',
        ]);

        $body = $this->decodeResponse();
        $meta = $body['meta'];

        $this->assertSame('2025-01-01', $meta['period']['from']);
        $this->assertSame('2025-12-31', $meta['period']['to']);
        $this->assertSame(count($body['data']), $meta['count']);
        $this->assertArrayHasKey('generatedAt', $meta);
    }

    public function testReturnsBadRequestWhenFromIsAfterTo(): void
    {
        $this->client->request('GET', '/api/pos/summary', [
            'from' => '2025-12-31',
            'to' => '2025-01-01',
        ]);

        $this->assertResponseStatusCodeSame(400);

        $body = $this->decodeResponse();
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('Date "from" must be before or equal to "to".', $body['error']);
    }

    public function testReturnsBadRequestForInvalidDateFormat(): void
    {
        $this->client->request('GET', '/api/pos/summary', [
            'from' => 'invalid-date',
            'to' => '2025-01-31',
        ]);

        $this->assertResponseStatusCodeSame(400);

        $body = $this->decodeResponse();
        $this->assertArrayHasKey('error', $body);
    }

    public function testResponseContainsExpectedFields(): void
    {
        $this->client->request('GET', '/api/pos/summary', [
            'from' => '2025-01-01',
            'to' => '2025-12-31',
        ]);

        $this->assertResponseIsSuccessful();

        $body = $this->decodeResponse();
        $this->assertNotEmpty($body['data']);

        $firstItem = $body['data'][0];
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('name', $firstItem);
        $this->assertArrayHasKey('orderCount', $firstItem);
        $this->assertArrayHasKey('totalRevenue', $firstItem);
        $this->assertArrayHasKey('averageOrderValue', $firstItem);
    }

    public function testInactivePosAreExcluded(): void
    {
        $this->client->request('GET', '/api/pos/summary', [
            'from' => '2025-01-01',
            'to' => '2025-12-31',
        ]);

        $data = $this->decodeResponse()['data'];

        $names = array_column($data, 'name');
        $this->assertNotContains('Closed Store', $names);
        $this->assertContains('Active Store', $names);
    }

    public function testPosWithZeroOrdersInPeriod(): void
    {
        $this->client->request('GET', '/api/pos/summary', [
            'from' => '2099-01-01',
            'to' => '2099-12-31',
        ]);

        $this->assertResponseIsSuccessful();

        $data = $this->decodeResponse()['data'];

        foreach ($data as $item) {
            $this->assertSame(0, $item['orderCount']);
            $this->assertEquals(0, $item['totalRevenue']);
            $this->assertEquals(0, $item['averageOrderValue']);
        }
    }

    public function testAverageOrderValueCalculation(): void
    {
        $this->client->request('GET', '/api/pos/summary', [
            'from' => '2025-01-01',
            'to' => '2025-12-31',
        ]);

        $data = $this->decodeResponse()['data'];

        foreach ($data as $item) {
            if ($item['orderCount'] > 0) {
                $expectedAvg = round($item['totalRevenue'] / $item['orderCount'], 2);
                $this->assertEquals($expectedAvg, $item['averageOrderValue']);
            }
        }
    }

    public function testResultsAreSortedByName(): void
    {
        $this->client->request('GET', '/api/pos/summary', [
            'from' => '2025-01-01',
            'to' => '2025-12-31',
        ]);

        $data = $this->decodeResponse()['data'];
        $names = array_column($data, 'name');
        $sorted = $names;
        sort($sorted);

        $this->assertSame($sorted, $names);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(): array
    {
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    private function resetDatabase(): void
    {
        $connection = $this->em->getConnection();
        $schemaManager = $connection->createSchemaManager();

        if ($schemaManager->tablesExist(['orders'])) {
            $connection->executeStatement('DROP TABLE orders CASCADE');
        }
        if ($schemaManager->tablesExist(['point_of_sale'])) {
            $connection->executeStatement('DROP TABLE point_of_sale CASCADE');
        }

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    private function loadTestData(): void
    {
        $activePos = new PointOfSale();
        $activePos->setName('Active Store');
        $activePos->setIsActive(true);
        $this->em->persist($activePos);

        $emptyPos = new PointOfSale();
        $emptyPos->setName('Empty Store');
        $emptyPos->setIsActive(true);
        $this->em->persist($emptyPos);

        $inactivePos = new PointOfSale();
        $inactivePos->setName('Closed Store');
        $inactivePos->setIsActive(false);
        $this->em->persist($inactivePos);

        // 5 orders x 100.00 = predictable totals for assertion
        for ($i = 0; $i < 5; ++$i) {
            $order = new Order();
            $order->setPointOfSale($activePos);
            $order->setTotalAmount('100.00');
            $order->setCreatedAt(new \DateTimeImmutable('2025-06-15'));
            $this->em->persist($order);
        }

        $inactiveOrder = new Order();
        $inactiveOrder->setPointOfSale($inactivePos);
        $inactiveOrder->setTotalAmount('999.99');
        $inactiveOrder->setCreatedAt(new \DateTimeImmutable('2025-06-15'));
        $this->em->persist($inactiveOrder);

        $this->em->flush();
    }
}
