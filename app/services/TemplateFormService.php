<?php

declare(strict_types=1);

namespace App\Services;

class TemplateFormService
{
    public function decodeSchema(?string $json): array
    {
        if (!$json) {
            return WordTemplateParser::defaultSchema();
        }
        $data = json_decode($json, true);
        return is_array($data) && !empty($data['rows']) ? $data : WordTemplateParser::defaultSchema();
    }

    public function decodeData(?string $json): array
    {
        if (!$json) {
            return [];
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    /** @param array<string, mixed> $postCells */
    public function parsePostedCells(array $postCells): array
    {
        $out = [];
        foreach ($postCells as $id => $value) {
            $id = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $id);
            if ($id !== '') {
                $out[$id] = trim((string) $value);
            }
        }
        return $out;
    }

    public function mergeForDisplay(array $schema, array $formData): array
    {
        $rows = $schema['rows'] ?? [];
        foreach ($rows as &$row) {
            foreach ($row['cells'] as &$cell) {
                $id = $cell['id'] ?? '';
                if (!empty($cell['editable']) && isset($formData[$id])) {
                    $cell['value'] = $formData[$id];
                } elseif (!empty($cell['editable'])) {
                    $cell['value'] = '';
                } else {
                    $cell['value'] = $cell['text'] ?? '';
                }
            }
        }
        return $rows;
    }

    public function calcProgress(array $schema, array $formData): int
    {
        $editable = 0;
        $filled = 0;
        foreach ($schema['rows'] ?? [] as $row) {
            foreach ($row['cells'] ?? [] as $cell) {
                if (empty($cell['editable'])) {
                    continue;
                }
                $editable++;
                $id = $cell['id'] ?? '';
                if ($id !== '' && trim($formData[$id] ?? '') !== '') {
                    $filled++;
                }
            }
        }
        if ($editable === 0) {
            return 0;
        }
        return (int) round(($filled / $editable) * 100);
    }
}
