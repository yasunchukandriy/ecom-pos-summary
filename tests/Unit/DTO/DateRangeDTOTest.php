<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\DateRangeDTO;
use PHPUnit\Framework\TestCase;

final class DateRangeDTOTest extends TestCase
{
    public function testCreateFromValidDates(): void
    {
        $dto = new DateRangeDTO(
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-01-31'),
        );

        $this->assertEquals('2025-01-01', $dto->from->format('Y-m-d'));
        $this->assertEquals('2025-01-31', $dto->to->format('Y-m-d'));
    }

    public function testThrowsExceptionWhenFromIsAfterTo(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Date "from" must be before or equal to "to".');

        new DateRangeDTO(
            new \DateTimeImmutable('2025-02-01'),
            new \DateTimeImmutable('2025-01-01'),
        );
    }

    public function testFromQueryParamsWithBothParams(): void
    {
        $dto = DateRangeDTO::fromQueryParams('2025-03-01', '2025-03-31');

        $this->assertEquals('2025-03-01', $dto->from->format('Y-m-d'));
        $this->assertEquals('2025-03-31', $dto->to->format('Y-m-d'));
    }

    public function testFromQueryParamsWithNullDefaultsToCurrentMonth(): void
    {
        $dto = DateRangeDTO::fromQueryParams(null, null);
        $now = new \DateTimeImmutable();

        $this->assertEquals(
            $now->modify('first day of this month')->format('Y-m-d'),
            $dto->from->format('Y-m-d')
        );
        $this->assertEquals(
            $now->modify('last day of this month')->format('Y-m-d'),
            $dto->to->format('Y-m-d')
        );
    }

    public function testFromQueryParamsWithOnlyFrom(): void
    {
        $dto = DateRangeDTO::fromQueryParams('2025-06-15', null);
        $now = new \DateTimeImmutable();

        $this->assertEquals('2025-06-15', $dto->from->format('Y-m-d'));
        $this->assertEquals(
            $now->modify('last day of this month')->format('Y-m-d'),
            $dto->to->format('Y-m-d')
        );
    }

    public function testFromQueryParamsWithOnlyTo(): void
    {
        $now = new \DateTimeImmutable();
        $futureDate = $now->modify('last day of this month')->format('Y-m-d');

        $dto = DateRangeDTO::fromQueryParams(null, $futureDate);

        $this->assertEquals(
            $now->modify('first day of this month')->format('Y-m-d'),
            $dto->from->format('Y-m-d')
        );
        $this->assertEquals($futureDate, $dto->to->format('Y-m-d'));
    }

    public function testFromQueryParamsThrowsOnInvalidDateFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format.');

        DateRangeDTO::fromQueryParams('not-a-date', '2025-01-31');
    }

    public function testFromQueryParamsThrowsWhenFromAfterTo(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Date "from" must be before or equal to "to".');

        DateRangeDTO::fromQueryParams('2025-12-31', '2025-01-01');
    }

    public function testCurrentMonthFactory(): void
    {
        $dto = DateRangeDTO::currentMonth();
        $now = new \DateTimeImmutable();

        $this->assertEquals(
            $now->modify('first day of this month')->format('Y-m-d'),
            $dto->from->format('Y-m-d')
        );
        $this->assertEquals('00:00:00', $dto->from->format('H:i:s'));

        $this->assertEquals(
            $now->modify('last day of this month')->format('Y-m-d'),
            $dto->to->format('Y-m-d')
        );
        $this->assertEquals('23:59:59', $dto->to->format('H:i:s'));
    }

    public function testEqualDatesAreAllowed(): void
    {
        $date = new \DateTimeImmutable('2025-05-15');
        $dto = new DateRangeDTO($date, $date);

        $this->assertEquals($dto->from, $dto->to);
    }
}
