<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Immutable value object representing a date range for filtering.
 */
final readonly class DateRangeDTO
{
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
    ) {
        if ($this->from > $this->to) {
            throw new \InvalidArgumentException(
                'Date "from" must be before or equal to "to".'
            );
        }
    }

    /**
     * Builds a DateRange from raw query string values.
     */
    public static function fromQueryParams(?string $from, ?string $to): self
    {
        if ($from === null && $to === null) {
            return self::currentMonth();
        }

        try {
            return new self(
                new \DateTimeImmutable($from ?? (new \DateTimeImmutable('first day of this month'))->format('Y-m-d')),
                new \DateTimeImmutable($to ?? (new \DateTimeImmutable('last day of this month'))->format('Y-m-d')),
            );
        } catch (\DateMalformedStringException) {
            throw new \InvalidArgumentException('Invalid date format. Expected YYYY-MM-DD.');
        }
    }

    public static function currentMonth(): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            $now->modify('first day of this month')->setTime(0, 0),
            $now->modify('last day of this month')->setTime(23, 59, 59),
        );
    }

}
