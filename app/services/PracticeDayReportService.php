<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Workload;

class PracticeDayReportService
{
    private WorkloadScheduleService $schedule;

    public function __construct(?WorkloadScheduleService $schedule = null)
    {
        $this->schedule = $schedule ?? new WorkloadScheduleService();
    }

    /**
     * @return list<array{
     *   workload_id:int,
     *   practice_name:string,
     *   supervisor:string,
     *   group:string,
     *   time:string,
     *   auditorium:string
     * }>
     */
    public function forDate(string $dateYmd): array
    {
        $target = $this->normalizeDate($dateYmd);
        if ($target === '') {
            return [];
        }

        $rows = [];
        foreach ((new Workload())->allWithSchedule() as $workload) {
            $entries = $this->schedule->entriesForWorkload($workload);
            foreach ($entries as $entry) {
                if (!$this->schedule->rowHasAnyValue($entry)) {
                    continue;
                }
                if ($this->normalizeDate((string) ($entry['date'] ?? '')) !== $target) {
                    continue;
                }

                $rows[] = [
                    'workload_id'   => (int) $workload['id'],
                    'practice_name' => trim((string) ($workload['module_name'] ?? '')),
                    'supervisor'    => trim((string) ($workload['teacher_name'] ?? '')),
                    'group'         => trim((string) ($workload['study_group'] ?? '')),
                    'time'          => $this->formatTimeRange($entry),
                    'auditorium'    => trim((string) ($entry['place'] ?? '')),
                    '_sort_minutes' => $this->timeSortKey($entry),
                ];
            }
        }

        usort($rows, static function (array $a, array $b): int {
            $ta = $a['_sort_minutes'] ?? 9999;
            $tb = $b['_sort_minutes'] ?? 9999;
            if ($ta !== $tb) {
                return $ta <=> $tb;
            }
            return strcmp($a['supervisor'], $b['supervisor']);
        });

        foreach ($rows as &$row) {
            unset($row['_sort_minutes']);
        }
        unset($row);

        return $rows;
    }

    public function normalizeDate(string $date): string
    {
        $date = trim($date);
        if ($date === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $date, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }
        return $date;
    }

    /** @param array<string, mixed> $entry */
    private function formatTimeRange(array $entry): string
    {
        $start = trim((string) ($entry['time_start'] ?? ''));
        $end = trim((string) ($entry['time_end'] ?? ''));
        if ($start === '' && $end === '') {
            return '';
        }
        if ($start !== '' && $end !== '') {
            return $start . ' – ' . $end;
        }
        return $start !== '' ? $start : $end;
    }

    /** @param array<string, mixed> $entry */
    private function timeSortKey(array $entry): int
    {
        $start = trim((string) ($entry['time_start'] ?? ''));
        if ($start === '' || !preg_match('/^(\d{1,2}):(\d{2})/', $start, $m)) {
            return 9999;
        }
        return (int) $m[1] * 60 + (int) $m[2];
    }
}
