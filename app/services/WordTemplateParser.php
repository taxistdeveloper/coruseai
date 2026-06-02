<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Извлекает таблицы из .docx и строит схему формы для веб-заполнения.
 */
class WordTemplateParser
{
    private const MAX_ROWS = 80;
    private const MAX_COLS = 20;

    public function parseFile(string $absolutePath): array
    {
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if ($ext === 'docx') {
            $schema = $this->parseDocx($absolutePath);
            if (!empty($schema['rows'])) {
                return $schema;
            }
        }
        return self::defaultSchema();
    }

    public function parseDocx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return ['rows' => []];
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (!$xml) {
            return ['rows' => []];
        }

        $dom = new \DOMDocument();
        @$dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $rows = [];
        $rowIndex = 0;
        $tables = $xpath->query('//w:tbl');

        foreach ($tables as $table) {
            if ($rowIndex >= self::MAX_ROWS) {
                break;
            }
            $trList = $xpath->query('.//w:tr', $table);
            foreach ($trList as $tr) {
                if ($rowIndex >= self::MAX_ROWS) {
                    break 2;
                }
                $cells = [];
                $colIndex = 0;
                $tcList = $xpath->query('./w:tc', $tr);
                foreach ($tcList as $tc) {
                    if ($colIndex >= self::MAX_COLS) {
                        break;
                    }
                    $text = $this->extractCellText($xpath, $tc);
                    $cells[] = [
                        'id'       => 'r' . $rowIndex . 'c' . $colIndex,
                        'text'     => $text,
                        'editable' => $this->isEditableCell($text),
                    ];
                    $colIndex++;
                }
                if ($cells !== []) {
                    $rows[] = ['cells' => $cells];
                    $rowIndex++;
                }
            }
        }

        return ['rows' => $rows, 'source' => 'docx'];
    }

    private function extractCellText(\DOMXPath $xpath, \DOMNode $tc): string
    {
        $parts = [];
        foreach ($xpath->query('.//w:t', $tc) as $node) {
            $parts[] = $node->nodeValue;
        }
        return trim(preg_replace('/\s+/u', ' ', implode('', $parts)) ?? '');
    }

    private function isEditableCell(string $text): bool
    {
        if ($text === '') {
            return true;
        }
        if (preg_match('/^[_\s.…:·-]+$/u', $text)) {
            return true;
        }
        if (preg_match('/^\(.*\)$/u', $text) && mb_strlen($text) < 40) {
            return true;
        }
        return false;
    }

    public static function defaultSchema(): array
    {
        return [
            'source' => 'default',
            'rows'   => [
                ['cells' => [
                    ['id' => 'r0c0', 'text' => 'График практики преподавателя', 'editable' => false, 'colspan' => 4],
                ]],
                ['cells' => [
                    ['id' => 'r1c0', 'text' => 'ФИО', 'editable' => false],
                    ['id' => 'r1c1', 'text' => '', 'editable' => true],
                    ['id' => 'r1c2', 'text' => 'Кафедра', 'editable' => false],
                    ['id' => 'r1c3', 'text' => '', 'editable' => true],
                ]],
                ['cells' => [
                    ['id' => 'r2c0', 'text' => 'Модуль', 'editable' => false],
                    ['id' => 'r2c1', 'text' => '', 'editable' => true],
                    ['id' => 'r2c2', 'text' => 'Часов', 'editable' => false],
                    ['id' => 'r2c3', 'text' => '', 'editable' => true],
                ]],
                ['cells' => [
                    ['id' => 'r3c0', 'text' => '№', 'editable' => false],
                    ['id' => 'r3c1', 'text' => 'Дата', 'editable' => false],
                    ['id' => 'r3c2', 'text' => 'Вид занятия', 'editable' => false],
                    ['id' => 'r3c3', 'text' => 'Часы', 'editable' => false],
                ]],
                ['cells' => [
                    ['id' => 'r4c0', 'text' => '1', 'editable' => false],
                    ['id' => 'r4c1', 'text' => '', 'editable' => true],
                    ['id' => 'r4c2', 'text' => '', 'editable' => true],
                    ['id' => 'r4c3', 'text' => '', 'editable' => true],
                ]],
                ['cells' => [
                    ['id' => 'r5c0', 'text' => '2', 'editable' => false],
                    ['id' => 'r5c1', 'text' => '', 'editable' => true],
                    ['id' => 'r5c2', 'text' => '', 'editable' => true],
                    ['id' => 'r5c3', 'text' => '', 'editable' => true],
                ]],
                ['cells' => [
                    ['id' => 'r6c0', 'text' => '3', 'editable' => false],
                    ['id' => 'r6c1', 'text' => '', 'editable' => true],
                    ['id' => 'r6c2', 'text' => '', 'editable' => true],
                    ['id' => 'r6c3', 'text' => '', 'editable' => true],
                ]],
                ['cells' => [
                    ['id' => 'r7c0', 'text' => '4', 'editable' => false],
                    ['id' => 'r7c1', 'text' => '', 'editable' => true],
                    ['id' => 'r7c2', 'text' => '', 'editable' => true],
                    ['id' => 'r7c3', 'text' => '', 'editable' => true],
                ]],
                ['cells' => [
                    ['id' => 'r8c0', 'text' => 'Итого часов', 'editable' => false],
                    ['id' => 'r8c1', 'text' => '', 'editable' => true],
                    ['id' => 'r8c2', 'text' => 'Подпись', 'editable' => false],
                    ['id' => 'r8c3', 'text' => '', 'editable' => true],
                ]],
            ],
        ];
    }
}
