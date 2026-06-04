<?php

declare(strict_types=1);

namespace App\Services;

class KazakhstanCalendarService
{
    /** @var array<string, true> */
    private array $holidays;

    public function __construct()
    {
        $dates = config('kazakhstan_holidays.dates', []);
        $this->holidays = [];
        foreach ($dates as $d) {
            $d = trim((string) $d);
            if ($d !== '') {
                $this->holidays[$d] = true;
            }
        }
    }

    public function isWeekend(string $dateYmd): bool
    {
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $dateYmd);
        if (!$dt) {
            return false;
        }
        $n = (int) $dt->format('N');
        return $n >= 6;
    }

    public function isHoliday(string $dateYmd): bool
    {
        return isset($this->holidays[$dateYmd]);
    }

    /** @return 'weekend'|'holiday'|null */
    public function exclusionReason(string $dateYmd): ?string
    {
        if ($dateYmd === '') {
            return null;
        }
        if ($this->isWeekend($dateYmd)) {
            return 'weekend';
        }
        if ($this->isHoliday($dateYmd)) {
            return 'holiday';
        }
        return null;
    }

    public function exclusionLabel(?string $reason): string
    {
        return match ($reason) {
            'weekend' => 'не считается: суббота или воскресенье',
            'holiday' => 'не считается: праздник РК',
            default   => '',
        };
    }

    /** @return list<string> */
    public function holidayDates(): array
    {
        return array_keys($this->holidays);
    }
}
