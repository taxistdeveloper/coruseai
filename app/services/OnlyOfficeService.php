<?php

declare(strict_types=1);

namespace App\Services;

class OnlyOfficeService
{
    public function isEnabled(): bool
    {
        return (bool) config('onlyoffice.enabled', false);
    }

    public function documentServerUrl(): string
    {
        return rtrim((string) config('onlyoffice.document_server_url', ''), '/');
    }

    public function appUrl(): string
    {
        return rtrim((string) config('onlyoffice.app_url', config('url', '')), '/');
    }

    public function documentKey(int $workloadId, ?int $version = null): string
    {
        $path = (new WorkloadDocumentService())->absolutePath(
            (new \App\Models\Workload())->find($workloadId)['document_path'] ?? null
        );
        $mtime = $path && file_exists($path) ? filemtime($path) : time();
        $ver = $version ?? (int) $mtime;
        return 'wl' . $workloadId . '_v' . $ver;
    }

    public function downloadToken(int $workloadId, int $ttlSeconds = 86400): string
    {
        $exp = time() + $ttlSeconds;
        $secret = (string) config('onlyoffice.token_secret', 'secret');
        $sig = hash_hmac('sha256', $workloadId . '|' . $exp, $secret);
        return base64_encode($workloadId . '|' . $exp . '|' . $sig);
    }

    public function validateDownloadToken(string $token, int $workloadId): bool
    {
        $decoded = base64_decode($token, true);
        if (!$decoded || !str_contains($decoded, '|')) {
            return false;
        }
        [$id, $exp, $sig] = explode('|', $decoded, 3);
        if ((int) $id !== $workloadId || time() > (int) $exp) {
            return false;
        }
        $secret = (string) config('onlyoffice.token_secret', 'secret');
        $expected = hash_hmac('sha256', $id . '|' . $exp, $secret);
        return hash_equals($expected, $sig);
    }

    public function documentDownloadUrl(int $workloadId): string
    {
        $token = urlencode($this->downloadToken($workloadId));
        return $this->appUrl() . '/documents/workload/' . $workloadId . '?token=' . $token;
    }

    public function callbackUrl(): string
    {
        return $this->appUrl() . '/documents/callback';
    }

    /**
     * @param array{fullname?: string, id?: int} $user
     */
    public function buildEditorConfig(int $workloadId, string $title, string $mode, array $user): array
    {
        $canEdit = $mode === 'edit';
        $config = [
            'document' => [
                'fileType' => 'docx',
                'key'      => $this->documentKey($workloadId),
                'title'    => $title,
                'url'      => $this->documentDownloadUrl($workloadId),
            ],
            'documentType' => 'word',
            'editorConfig' => [
                'callbackUrl' => $this->callbackUrl(),
                'lang'        => 'ru',
                'mode'        => $canEdit ? 'edit' : 'view',
                'user'        => [
                    'id'   => (string) ($user['id'] ?? '0'),
                    'name' => $user['fullname'] ?? 'User',
                ],
                'customization' => [
                    'autosave' => true,
                    'forcesave' => true,
                ],
            ],
            'height' => '100%',
            'width'  => '100%',
        ];

        if ($this->jwtSecret() !== '') {
            $config['token'] = $this->signJwt($config);
        }

        return $config;
    }

    public function signJwt(array $payload): string
    {
        $secret = $this->jwtSecret();
        $header = $this->base64Url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = $this->base64Url(json_encode($payload));
        $signature = $this->base64Url(hash_hmac('sha256', $header . '.' . $body, $secret, true));
        return $header . '.' . $body . '.' . $signature;
    }

    public function processCallback(array $body): array
    {
        $status = (int) ($body['status'] ?? 0);
        $key = (string) ($body['key'] ?? '');
        $url = (string) ($body['url'] ?? '');

        if (!preg_match('/^wl(\d+)_v/', $key, $m)) {
            return ['error' => 1];
        }
        $workloadId = (int) $m[1];

        // 2 = готов к сохранению, 6 = редактирование завершено
        if (in_array($status, [2, 6], true) && $url !== '') {
            $this->saveFromUrl($workloadId, $url);
            (new \App\Models\Workload())->update($workloadId, ['status' => 'in_progress']);
        }

        return ['error' => 0];
    }

    private function saveFromUrl(int $workloadId, string $url): void
    {
        $docService = new WorkloadDocumentService();
        $relative = $docService->ensureForWorkload($workloadId);
        if (!$relative) {
            return;
        }
        $abs = ROOT_PATH . '/' . ltrim($relative, '/');
        $content = file_get_contents($url);
        if ($content !== false) {
            file_put_contents($abs, $content);
        }
    }

    private function jwtSecret(): string
    {
        return (string) config('onlyoffice.jwt_secret', '');
    }

    private function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
