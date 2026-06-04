<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ScheduleTemplate;
use App\Models\Workload;

class GrafikDocxService
{
    private const W_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private WorkloadScheduleService $schedule;

    public function __construct(?WorkloadScheduleService $schedule = null)
    {
        $this->schedule = $schedule ?? new WorkloadScheduleService();
    }

    public function resolveTemplatePath(): string
    {
        $template = (new ScheduleTemplate())->active();
        if ($template && !empty($template['file_path'])) {
            $path = ROOT_PATH . '/' . ltrim($template['file_path'], '/');
            if (is_file($path)) {
                return $path;
            }
        }

        $fallback = ROOT_PATH . '/grafik.docx';
        if (!is_file($fallback)) {
            throw new \RuntimeException('Файл grafik.docx не найден в корне проекта.');
        }

        return $fallback;
    }

    /** @param array<string, mixed> $workload */
    public function render(array $workload): string
    {
        $src = $this->resolveTemplatePath();
        $tmp = tempnam(sys_get_temp_dir(), 'grafik_');
        if ($tmp === false || !copy($src, $tmp)) {
            throw new \RuntimeException('Не удалось подготовить документ.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($tmp) !== true) {
            @unlink($tmp);
            throw new \RuntimeException('Не удалось открыть шаблон DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            @unlink($tmp);
            throw new \RuntimeException('Повреждённый шаблон DOCX.');
        }

        $filled = $this->fillDocumentXml($xml, $workload);
        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $filled);
        $zip->close();

        $bytes = file_get_contents($tmp);
        @unlink($tmp);
        if ($bytes === false) {
            throw new \RuntimeException('Не удалось сформировать DOCX.');
        }

        return $bytes;
    }

    /** @param array<string, mixed> $workload */
    public function suggestFilename(array $workload): string
    {
        $parts = array_filter([
            $workload['teacher_name'] ?? 'grafik',
            $workload['module_name'] ?? '',
            isset($workload['id']) ? (string) $workload['id'] : '',
        ]);
        $base = preg_replace('/[^\p{L}\p{N}_-]+/u', '_', implode('_', $parts)) ?: 'grafik';
        $base = trim($base, '_');

        return $base . '.docx';
    }

    /** @param array<string, mixed> $workload */
    public function fillDocumentXml(string $xml, array $workload): string
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        if (!@$dom->loadXML($xml)) {
            throw new \RuntimeException('Не удалось разобрать шаблон DOCX.');
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', self::W_NS);

        $entries = $this->schedule->entriesForWorkload($workload);
        $entries = array_values(array_filter($entries, fn (array $e): bool => $this->schedule->rowHasAnyValue($e)));
        if ($entries === []) {
            $entries = [$this->schedule->emptyEntry((string) ($workload['module_name'] ?? ''))];
        }

        $practiceName = trim((string) ($workload['module_name'] ?? ''));
        $group = trim((string) ($workload['study_group'] ?? ''));
        $teacher = trim((string) ($workload['teacher_name'] ?? ''));
        $moduleLine = $this->moduleLineText($entries, $practiceName);
        $period = $this->periodText($entries, (string) ($workload['deadline'] ?? ''));

        $this->replacePracticeName($xpath, $practiceName);
        $this->replaceGroup($xpath, $group);
        $this->replaceModuleLine($xpath, $moduleLine);
        $this->replaceSupervisor($xpath, $teacher);
        if ($period !== '') {
            $this->replaceInParagraphContaining($xpath, 'Сроки проведения', $period, replaceAll: true);
        }

        $this->removePracticeTypeBlock($xpath);
        $this->fillScheduleTable($dom, $xpath, $entries);
        $this->stripTableHeaderRepeat($xpath);
        $this->removeEmptyParagraphsAfterTable($xpath);
        $this->removeDuplicateSectionFrom($xpath, 'Согласовано');
        $this->removeExtraTables($xpath);
        $this->trimBodyTail($xpath);

        return $dom->saveXML();
    }

    /** @param list<array<string, mixed>> $entries */
    private function moduleLineText(array $entries, string $fallback): string
    {
        foreach ($entries as $entry) {
            $text = $this->schedule->formatCurriculum($entry);
            if ($text !== '') {
                return $text;
            }
        }

        return $fallback;
    }

    /** @param list<array<string, mixed>> $entries */
    private function periodText(array $entries, string $deadline): string
    {
        $dates = [];
        foreach ($entries as $entry) {
            $d = trim((string) ($entry['date'] ?? ''));
            if ($d !== '') {
                $dates[] = $d;
            }
        }
        sort($dates);
        $from = $dates !== [] ? $this->formatDateRu($dates[0]) : '';
        $to = $dates !== [] ? $this->formatDateRu($dates[count($dates) - 1]) : '';
        if ($from === '' && $deadline !== '') {
            $to = $this->formatDateRu($deadline);
        }
        if ($from === '' && $to === '') {
            return '';
        }
        if ($from === $to || $to === '') {
            return 'с «' . ($from ?: '___') . '»';
        }

        return 'с «' . $from . '» по «' . $to . '»';
    }

    private function formatDateRu(string $iso): string
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $iso, $m)) {
            return sprintf('%02d.%02d.%04d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        return $iso;
    }

    private function replacePracticeName(\DOMXPath $xpath, string $value): void
    {
        if ($value === '') {
            return;
        }
        $paragraphs = $xpath->query('//w:body/w:p');
        if ($paragraphs === false) {
            return;
        }
        for ($i = 0; $i < $paragraphs->length - 1; $i++) {
            $nextText = $this->paragraphText($xpath, $paragraphs->item($i + 1));
            if (!str_contains($nextText, 'наименование практики')) {
                continue;
            }
            $this->replaceUnderscoresInParagraph($xpath, $paragraphs->item($i), $value);
            return;
        }
    }

    private function replaceGroup(\DOMXPath $xpath, string $value): void
    {
        if ($value === '') {
            return;
        }
        foreach ($xpath->query('//w:p') as $paragraph) {
            $text = $this->paragraphText($xpath, $paragraph);
            if (!str_contains($text, 'группы')) {
                continue;
            }
            foreach ($xpath->query('.//w:t', $paragraph) as $node) {
                if (preg_match('/^_{3,}$/u', (string) $node->nodeValue)) {
                    $node->nodeValue = $value;
                    return;
                }
            }
        }
    }

    private function replaceModuleLine(\DOMXPath $xpath, string $value): void
    {
        if ($value === '') {
            return;
        }
        $paragraphs = $xpath->query('//w:body/w:p');
        if ($paragraphs === false) {
            return;
        }
        for ($i = 0; $i < $paragraphs->length - 1; $i++) {
            $nextText = $this->paragraphText($xpath, $paragraphs->item($i + 1));
            if (!str_contains($nextText, 'наименование модуля')) {
                continue;
            }
            $this->replaceUnderscoresInParagraph($xpath, $paragraphs->item($i), $value);
            return;
        }
    }

    private function replaceSupervisor(\DOMXPath $xpath, string $value): void
    {
        if ($value === '') {
            return;
        }
        foreach ($xpath->query('//w:p') as $paragraph) {
            $text = $this->paragraphText($xpath, $paragraph);
            if (!str_contains($text, 'Руководитель практики')
                && !str_contains($text, 'Руководители производственного обучения')) {
                continue;
            }
            foreach ($xpath->query('.//w:t', $paragraph) as $node) {
                $nodeText = (string) $node->nodeValue;
                if (str_contains($nodeText, ':')) {
                    $node->nodeValue = preg_replace('/_+/', '', $nodeText) . ' ' . $value;
                    return;
                }
                if (preg_match('/^_{3,}$/u', $nodeText)) {
                    $node->nodeValue = $value;
                    return;
                }
            }
        }
    }

    private function replaceUnderscoresInParagraph(\DOMXPath $xpath, ?\DOMNode $paragraph, string $value): void
    {
        if (!$paragraph instanceof \DOMElement) {
            return;
        }
        foreach ($xpath->query('.//w:t', $paragraph) as $node) {
            if (preg_match('/^_{3,}$/u', (string) $node->nodeValue)) {
                $node->nodeValue = $value;
                return;
            }
        }
    }

    private function replaceInParagraphContaining(
        \DOMXPath $xpath,
        string $marker,
        string $value,
        bool $afterColon = false,
        bool $replaceAll = false
    ): void {
        if ($value === '') {
            return;
        }

        foreach ($xpath->query('//w:p') as $paragraph) {
            $text = $this->paragraphText($xpath, $paragraph);
            if (!str_contains($text, $marker)) {
                continue;
            }

            if ($replaceAll) {
                $this->clearParagraphRuns($xpath, $paragraph);
                $this->appendRunText($xpath, $paragraph, $marker . ' ' . $value, cloneFrom: $paragraph);
                return;
            }

            if ($afterColon) {
                foreach ($xpath->query('.//w:t', $paragraph) as $node) {
                    if (str_contains((string) $node->nodeValue, ':')) {
                        $node->nodeValue = preg_replace('/_+$/', '', (string) $node->nodeValue) ?? $node->nodeValue;
                        $node->nodeValue = rtrim((string) $node->nodeValue) . ' ' . $value;
                        return;
                    }
                }
            }

            foreach ($xpath->query('.//w:t', $paragraph) as $node) {
                if (preg_match('/_{3,}/', (string) $node->nodeValue)) {
                    $node->nodeValue = $value;
                    return;
                }
            }
        }
    }

    /** @param list<array<string, mixed>> $entries */
    private function fillScheduleTable(\DOMDocument $dom, \DOMXPath $xpath, array $entries): void
    {
        $table = $xpath->query('//w:tbl')->item(0);
        if (!$table instanceof \DOMElement) {
            return;
        }

        $rows = $xpath->query('./w:tr', $table);
        if ($rows->length < 2) {
            return;
        }

        $templateRow = $rows->item(1);
        if (!$templateRow instanceof \DOMElement) {
            return;
        }

        $runTemplate = $xpath->query('.//w:r', $templateRow)->item(0);

        foreach ($entries as $index => $entry) {
            $row = $index === 0 ? $templateRow : $templateRow->cloneNode(true);
            if ($index > 0) {
                $table->appendChild($row);
            }

            $cells = $xpath->query('./w:tc', $row);
            if ($cells->length < 4) {
                continue;
            }

            $this->setCellText($dom, $cells->item(0), (string) ($index + 1), $runTemplate);
            $this->setCellText($dom, $cells->item(1), $this->schedule->formatCurriculum($entry), $runTemplate);
            $this->setCellText($dom, $cells->item(2), $this->formatDateTimeCell($entry), $runTemplate);
            $this->setCellText($dom, $cells->item(3), $this->formatPlaceCell($entry), $runTemplate);
        }
    }

    /** @param array<string, mixed> $entry */
    private function formatDateTimeCell(array $entry): string
    {
        $date = $this->formatDateRu(trim((string) ($entry['date'] ?? '')));
        $start = trim((string) ($entry['time_start'] ?? ''));
        $end = trim((string) ($entry['time_end'] ?? ''));
        if ($start !== '' && strlen($start) >= 5) {
            $start = substr($start, 0, 5);
        }
        if ($end !== '' && strlen($end) >= 5) {
            $end = substr($end, 0, 5);
        }

        $parts = [];
        if ($date !== '') {
            $parts[] = $date;
        }
        if ($start !== '' && $end !== '') {
            $parts[] = $start . '–' . $end;
        } elseif ($start !== '') {
            $parts[] = $start;
        }

        return implode(' ', $parts);
    }

    /** @param array<string, mixed> $entry */
    private function formatPlaceCell(array $entry): string
    {
        $place = trim((string) ($entry['place'] ?? ''));
        if (!empty($entry['is_dot'])) {
            $place = $place !== '' ? $place . ' (ДОТ)' : 'ДОТ';
        }

        return $place;
    }

    private function setCellText(\DOMDocument $dom, ?\DOMNode $cell, string $text, ?\DOMNode $runTemplate): void
    {
        if (!$cell instanceof \DOMElement) {
            return;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', self::W_NS);
        $paragraph = $xpath->query('./w:p', $cell)->item(0);
        if (!$paragraph instanceof \DOMElement) {
            return;
        }

        foreach ($xpath->query('./w:r', $paragraph) as $oldRun) {
            $paragraph->removeChild($oldRun);
        }

        $run = $dom->createElementNS(self::W_NS, 'w:r');
        if ($runTemplate instanceof \DOMElement) {
            $rPr = $xpath->query('./w:rPr', $runTemplate)->item(0);
            if ($rPr instanceof \DOMElement) {
                $run->appendChild($rPr->cloneNode(true));
            }
        }

        $t = $dom->createElementNS(self::W_NS, 'w:t');
        if ($text !== '' && preg_match('/^\s|\s$/u', $text)) {
            $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        }
        $t->appendChild($dom->createTextNode($text));
        $run->appendChild($t);
        $paragraph->appendChild($run);
    }

    private function paragraphText(\DOMXPath $xpath, \DOMNode $paragraph): string
    {
        $parts = [];
        foreach ($xpath->query('.//w:t', $paragraph) as $node) {
            $parts[] = $node->nodeValue;
        }

        return trim(preg_replace('/\s+/u', ' ', implode('', $parts)) ?? '');
    }

    private function removePracticeTypeBlock(\DOMXPath $xpath): void
    {
        $body = $xpath->query('//w:body')->item(0);
        if (!$body instanceof \DOMElement) {
            return;
        }

        foreach ($xpath->query('./w:p', $body) as $paragraph) {
            $text = $this->paragraphText($xpath, $paragraph);
            if ($text === '(вид практики)' && $paragraph->parentNode !== null) {
                $paragraph->parentNode->removeChild($paragraph);
            }
        }
    }

    /**
     * Удаляет второй лист-шаблон (дубликат формы), начиная с «Согласовано» и до конца body.
     */
    private function removeDuplicateSectionFrom(\DOMXPath $xpath, string $marker): void
    {
        $body = $xpath->query('//w:body')->item(0);
        if (!$body instanceof \DOMElement) {
            return;
        }

        $found = false;
        $toRemove = [];
        foreach ($body->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                if ($found) {
                    $toRemove[] = $child;
                }
                continue;
            }
            $local = $child->localName ?? '';
            if ($local === 'sectPr') {
                break;
            }
            if (!$found && $local === 'p' && str_contains($this->paragraphText($xpath, $child), $marker)) {
                $found = true;
                $toRemove[] = $child;
                continue;
            }
            if ($found) {
                $toRemove[] = $child;
            }
        }

        foreach ($toRemove as $node) {
            if ($node->parentNode !== null) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    private function removeExtraTables(\DOMXPath $xpath): void
    {
        $tables = $xpath->query('//w:tbl');
        if ($tables === false || $tables->length <= 1) {
            return;
        }
        for ($i = $tables->length - 1; $i >= 1; $i--) {
            $table = $tables->item($i);
            if ($table instanceof \DOMElement && $table->parentNode !== null) {
                $table->parentNode->removeChild($table);
            }
        }
    }

    /** Не дублировать шапку таблицы на следующей странице. */
    private function stripTableHeaderRepeat(\DOMXPath $xpath): void
    {
        foreach ($xpath->query('//w:tbl//w:tr') as $row) {
            if (!$row instanceof \DOMElement) {
                continue;
            }
            $trPr = $xpath->query('./w:trPr', $row)->item(0);
            if (!$trPr instanceof \DOMElement) {
                continue;
            }
            foreach ($xpath->query('./w:tblHeader', $trPr) as $header) {
                $trPr->removeChild($header);
            }
        }
    }

    private function removeEmptyParagraphsAfterTable(\DOMXPath $xpath): void
    {
        $table = $xpath->query('//w:tbl')->item(0);
        $body = $xpath->query('//w:body')->item(0);
        if (!$table instanceof \DOMElement || !$body instanceof \DOMElement) {
            return;
        }

        $node = $table->nextSibling;
        while ($node !== null) {
            $next = $node->nextSibling;
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                $body->removeChild($node);
                $node = $next;
                continue;
            }
            $local = $node->localName ?? '';
            if ($local === 'sectPr' || $local === 'bookmarkEnd') {
                $node = $next;
                continue;
            }
            if ($local !== 'p') {
                break;
            }
            if ($this->paragraphText($xpath, $node) !== '') {
                break;
            }
            $body->removeChild($node);
            $node = $next;
        }
    }

    private function trimBodyTail(\DOMXPath $xpath): void
    {
        $body = $xpath->query('//w:body')->item(0);
        if (!$body instanceof \DOMElement) {
            return;
        }

        while (true) {
            $last = $body->lastChild;
            if ($last === null) {
                break;
            }
            if ($last->nodeType !== XML_ELEMENT_NODE) {
                $body->removeChild($last);
                continue;
            }
            $local = $last->localName ?? '';
            if ($local === 'sectPr') {
                break;
            }
            if ($local === 'bookmarkEnd') {
                $body->removeChild($last);
                continue;
            }
            if ($local === 'p' && $this->paragraphText($xpath, $last) === '') {
                $body->removeChild($last);
                continue;
            }
            break;
        }
    }

    private function clearParagraphRuns(\DOMXPath $xpath, \DOMElement $paragraph): void
    {
        foreach ($xpath->query('./w:r', $paragraph) as $run) {
            $paragraph->removeChild($run);
        }
    }

    private function appendRunText(\DOMXPath $xpath, \DOMElement $paragraph, string $text, \DOMNode $cloneFrom): void
    {
        $doc = $paragraph->ownerDocument;
        if (!$doc instanceof \DOMDocument) {
            return;
        }

        $sample = $xpath->query('.//w:r', $cloneFrom)->item(0);
        $run = $doc->createElementNS(self::W_NS, 'w:r');
        if ($sample instanceof \DOMElement) {
            $rPr = $xpath->query('./w:rPr', $sample)->item(0);
            if ($rPr instanceof \DOMElement) {
                $run->appendChild($rPr->cloneNode(true));
            }
        }
        $t = $doc->createElementNS(self::W_NS, 'w:t');
        $t->appendChild($doc->createTextNode($text));
        $run->appendChild($t);
        $paragraph->appendChild($run);
    }

    /**
     * @param list<array<string, mixed>> $workloads
     */
    public function renderZip(array $workloads): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'grafik_zip_');
        if ($zipPath === false) {
            throw new \RuntimeException('Не удалось создать архив.');
        }
        @unlink($zipPath);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Не удалось создать ZIP.');
        }

        $usedNames = [];
        foreach ($workloads as $workload) {
            $name = $this->suggestFilename($workload);
            if (isset($usedNames[$name])) {
                $usedNames[$name]++;
                $name = preg_replace('/\.docx$/', '_' . $usedNames[$name] . '.docx', $name) ?? $name;
            } else {
                $usedNames[$name] = 1;
            }
            $zip->addFromString($name, $this->render($workload));
        }

        $zip->close();
        $bytes = file_get_contents($zipPath);
        @unlink($zipPath);
        if ($bytes === false) {
            throw new \RuntimeException('Не удалось прочитать ZIP.');
        }

        return $bytes;
    }

    /** @return list<array<string, mixed>> */
    public function workloadsForExport(string $search = '', ?int $teacherId = null, ?int $workloadId = null): array
    {
        $model = new Workload();
        if ($workloadId) {
            $row = $model->find($workloadId);
            return ($row && ($row['status'] ?? '') === 'submitted') ? [$row] : [];
        }

        return $model->submittedList($search, $teacherId, 10000, 0);
    }
}
