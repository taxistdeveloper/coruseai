<?php

declare(strict_types=1);

namespace App\Services;

class WorkloadScheduleService
{
    public const MIN_ROWS = 1;
    public const DEFAULT_ROWS = 5;

    private KazakhstanCalendarService $calendar;

    public function __construct(?KazakhstanCalendarService $calendar = null)
    {
        $this->calendar = $calendar ?? new KazakhstanCalendarService();
    }

    public function decodeFormData(mixed $json): array
    {
        if (!$json) {
            return ['entries' => []];
        }
        if (is_string($json)) {
            $data = json_decode($json, true);
        } else {
            $data = $json;
        }
        if (!is_array($data)) {
            return ['entries' => []];
        }
        return $data;
    }

    /** @return list<array{module_name:string,section:string,topic:string,date:string,time_start:string,time_end:string,hours:float,place:string,is_dot:bool}> */
    public function entriesForWorkload(array $workload): array
    {
        $data = $this->decodeFormData($workload['form_data'] ?? null);
        $entries = $data['entries'] ?? [];
        if ($entries !== []) {
            return $this->normalizeEntries($entries);
        }

        $rows = [];
        $module = trim((string) ($workload['module_name'] ?? ''));
        for ($i = 0; $i < self::DEFAULT_ROWS; $i++) {
            $rows[] = $this->emptyEntry($i === 0 ? $module : '');
        }
        return $rows;
    }

    /** @param array<int, array<string, mixed>> $posted */
    public function parsePostedEntries(array $posted): array
    {
        $entries = [];
        foreach ($posted as $row) {
            if (!is_array($row)) {
                continue;
            }
            $curriculum = $this->curriculumFromRow($row);
            $entries[] = [
                'module_name' => $curriculum,
                'section'     => '',
                'topic'       => '',
                'date'        => trim((string) ($row['date'] ?? '')),
                'time_start'  => trim((string) ($row['time_start'] ?? '')),
                'time_end'    => trim((string) ($row['time_end'] ?? '')),
                'place'       => trim((string) ($row['place'] ?? '')),
                'is_dot'      => !empty($row['is_dot']),
            ];
        }
        if ($entries === []) {
            $entries[] = $this->emptyEntry();
        }
        return $this->normalizeEntries($entries);
    }

    /** Объединённое наименование модуля, раздела и темы для отображения в форме. */
    public function formatCurriculum(array $entry): string
    {
        $parts = array_values(array_filter([
            trim((string) ($entry['module_name'] ?? '')),
            trim((string) ($entry['section'] ?? '')),
            trim((string) ($entry['topic'] ?? '')),
        ], static fn (string $p): bool => $p !== ''));

        return implode(', ', $parts);
    }

    /** @param array<string, mixed> $row */
    private function curriculumFromRow(array $row): string
    {
        if (array_key_exists('curriculum', $row)) {
            return trim((string) $row['curriculum']);
        }

        return $this->formatCurriculum([
            'module_name' => $row['module_name'] ?? '',
            'section'     => $row['section'] ?? '',
            'topic'       => $row['topic'] ?? '',
        ]);
    }

    public function emptyEntry(string $moduleName = ''): array
    {
        return [
            'module_name' => $moduleName,
            'section'     => '',
            'topic'       => '',
            'date'        => '',
            'time_start'  => '',
            'time_end'    => '',
            'hours'       => 0.0,
            'place'       => '',
            'is_dot'      => false,
        ];
    }

    /** @param list<array<string, mixed>> $entries */
    public function normalizeEntries(array $entries): array
    {
        $out = [];
        foreach ($entries as $entry) {
            $date = trim((string) ($entry['date'] ?? ''));
            $timeStart = trim((string) ($entry['time_start'] ?? ''));
            $timeEnd = trim((string) ($entry['time_end'] ?? ''));

            if ($timeStart === '' && !empty($entry['time'])) {
                $timeStart = trim((string) $entry['time']);
            }
            if ($timeEnd === '' && $timeStart !== '' && !empty($entry['hours'])) {
                $legacyHours = $this->parseHours($entry['hours']);
                if ($legacyHours > 0) {
                    $timeEnd = $this->addMinutesToTime($timeStart, (int) round($legacyHours * 60));
                }
            }
            if ($date === '' && $timeStart === '' && !empty($entry['datetime'])) {
                [$date, $timeStart] = $this->splitLegacyDatetime((string) $entry['datetime']);
            }

            $hours = $this->calcDurationHours($timeStart, $timeEnd);
            if ($hours <= 0 && !empty($entry['hours'])) {
                $hours = $this->parseHours($entry['hours']);
            }

            $out[] = [
                'module_name' => trim((string) ($entry['module_name'] ?? '')),
                'section'     => trim((string) ($entry['section'] ?? '')),
                'topic'       => trim((string) ($entry['topic'] ?? '')),
                'date'        => $date,
                'time_start'  => $timeStart,
                'time_end'    => $timeEnd,
                'hours'       => $hours,
                'place'       => trim((string) ($entry['place'] ?? '')),
                'is_dot'      => !empty($entry['is_dot']),
            ];
        }
        return $out;
    }

    public function calcDurationHours(string $timeStart, string $timeEnd): float
    {
        $startMin = $this->parseTimeToMinutes($timeStart);
        $endMin = $this->parseTimeToMinutes($timeEnd);
        if ($startMin === null || $endMin === null || $endMin <= $startMin) {
            return 0.0;
        }
        return round(($endMin - $startMin) / 60, 1);
    }

    private function parseTimeToMinutes(string $time): ?int
    {
        $time = trim($time);
        if ($time === '') {
            return null;
        }
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $m)) {
            return (int) $m[1] * 60 + (int) $m[2];
        }
        return null;
    }

    private function addMinutesToTime(string $timeStart, int $minutes): string
    {
        $startMin = $this->parseTimeToMinutes($timeStart);
        if ($startMin === null) {
            return '';
        }
        $total = $startMin + $minutes;
        $h = intdiv($total, 60) % 24;
        $m = $total % 60;
        return sprintf('%02d:%02d', $h, $m);
    }

    private function parseHours(mixed $value): float
    {
        if ($value === '' || $value === null) {
            return 0.0;
        }
        $h = (float) str_replace(',', '.', (string) $value);
        return max(0.0, round($h, 1));
    }

    /** @return array{0:string,1:string} */
    private function splitLegacyDatetime(string $datetime): array
    {
        $datetime = trim($datetime);
        if ($datetime === '') {
            return ['', ''];
        }
        if (preg_match('/^(.+?)\s+(\d{1,2}:\d{2}(?::\d{2})?)\s*$/u', $datetime, $m)) {
            return [trim($m[1]), trim($m[2])];
        }
        return [$datetime, ''];
    }

    /**
     * @param list<array<string, mixed>> $entries
     * @return array{
     *   limit:int,
     *   counted:float,
     *   excluded:float,
     *   remaining:float,
     *   percent:int,
     *   rows:list<array{index:int,hours:float,counted:bool,reason:?string,label:string}>
     * }
     */
    public function hoursSummary(array $entries, int $limitHours): array
    {
        $limitHours = max(0, $limitHours);
        $counted = 0.0;
        $excluded = 0.0;
        $rows = [];

        foreach ($entries as $index => $entry) {
            $hours = (float) ($entry['hours'] ?? 0);
            $date = trim((string) ($entry['date'] ?? ''));
            $reason = null;
            $counts = false;
            $label = '';

            if ($hours > 0 && $date !== '') {
                $reason = $this->calendar->exclusionReason($date);
                if ($reason === null) {
                    $counts = true;
                    $counted += $hours;
                    $label = '+' . $hours . ' ч. в зачёт';
                } else {
                    $excluded += $hours;
                    $label = $this->calendar->exclusionLabel($reason);
                }
            } elseif ($date !== '' && trim((string) ($entry['time_start'] ?? '')) !== '' && trim((string) ($entry['time_end'] ?? '')) !== '') {
                $label = 'проверьте время: окончание должно быть позже начала';
            }

            $rows[] = [
                'index'   => $index,
                'hours'   => $hours,
                'counted' => $counts,
                'reason'  => $reason,
                'label'   => $label,
            ];
        }

        $remaining = max(0.0, $limitHours - $counted);
        $percent = $limitHours > 0
            ? (int) min(100, round(($counted / $limitHours) * 100))
            : 0;

        return [
            'limit'     => $limitHours,
            'counted'   => round($counted, 1),
            'excluded'  => round($excluded, 1),
            'remaining' => round($remaining, 1),
            'percent'   => $percent,
            'rows'      => $rows,
        ];
    }

    /** @param list<array<string, mixed>> $entries */
    public function calcProgress(array $entries, int $limitHours): int
    {
        return $this->hoursSummary($entries, $limitHours)['percent'];
    }

    /** @param list<array<string, mixed>> $entries */
    public function validateForSubmit(array $entries, int $limitHours): ?string
    {
        $active = array_values(array_filter($entries, fn ($e) => $this->rowHasAnyValue($e)));
        if ($active === []) {
            return 'Добавьте хотя бы одну строку графика.';
        }

        foreach ($active as $i => $entry) {
            $n = $i + 1;
            if ($entry['module_name'] === '') {
                return "Строка {$n}: укажите наименование модуля, раздела и темы.";
            }
            if ($entry['date'] === '') {
                return "Строка {$n}: укажите дату проведения.";
            }
            if ($entry['time_start'] === '') {
                return "Строка {$n}: укажите время начала.";
            }
            if ($entry['time_end'] === '') {
                return "Строка {$n}: укажите время окончания.";
            }
            if ((float) ($entry['hours'] ?? 0) <= 0) {
                return "Строка {$n}: время окончания должно быть позже времени начала.";
            }

            $reason = $this->calendar->exclusionReason($entry['date']);
            if ($reason === 'weekend') {
                return "Строка {$n}: суббота и воскресенье не учитываются в нагрузке.";
            }
            if ($reason === 'holiday') {
                return "Строка {$n}: в этот день праздник в Казахстане — выберите другую дату.";
            }

            if ($entry['is_dot'] && $entry['place'] === '') {
                return "Строка {$n}: для ДОТ укажите место проведения (платформа, ссылка).";
            }
            if ($entry['place'] === '') {
                return "Строка {$n}: укажите место проведения.";
            }
        }

        $summary = $this->hoursSummary($entries, $limitHours);
        if ($summary['counted'] > $summary['limit']) {
            return sprintf(
                'Набрано %.1f ч. — больше лимита (%d ч.). Сократите интервалы или уберите лишние строки.',
                $summary['counted'],
                $summary['limit']
            );
        }
        if ($summary['limit'] > 0 && $summary['counted'] < $summary['limit'] - 0.05) {
            return sprintf(
                'Набрано %.1f из %d ч. Добавьте занятия в будние дни (без сб, вс и праздников РК).',
                $summary['counted'],
                $summary['limit']
            );
        }

        return null;
    }

    /** @param array<string, mixed> $entry */
    public function rowHasAnyValue(array $entry): bool
    {
        return $entry['module_name'] !== ''
            || $entry['section'] !== ''
            || $entry['topic'] !== ''
            || $entry['date'] !== ''
            || $entry['time_start'] !== ''
            || $entry['time_end'] !== ''
            || (float) ($entry['hours'] ?? 0) > 0
            || $entry['place'] !== ''
            || !empty($entry['is_dot']);
    }
}
