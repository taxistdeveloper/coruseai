<?php

declare(strict_types=1);

namespace App\Services;

class WordOnlineService
{
    public function isEnabled(): bool
    {
        return (bool) config('wordonline.enabled', false);
    }

    public function viewerEnabled(): bool
    {
        return $this->isEnabled() && (bool) config('wordonline.viewer_enabled', false);
    }

    public function canUseViewer(): bool
    {
        $base = $this->publicBaseUrl();
        return $this->viewerEnabled() && $base !== '' && str_starts_with($base, 'https://');
    }

    public function publicBaseUrl(): string
    {
        $url = trim((string) config('wordonline.public_base_url', ''));
        if ($url !== '') {
            return rtrim($url, '/');
        }
        $app = rtrim((string) config('url', ''), '/');
        if (str_starts_with($app, 'https://')) {
            return $app;
        }
        return '';
    }

    public function publicDocumentUrl(int $workloadId): string
    {
        $token = urlencode($this->downloadToken($workloadId));
        return $this->publicBaseUrl() . '/teacher/workloads/' . $workloadId . '/file?token=' . $token;
    }

    /** Office Online Viewer (только просмотр, бесплатно). */
    public function viewerEmbedUrl(int $workloadId): ?string
    {
        if (!$this->canUseViewer()) {
            return null;
        }
        $src = urlencode($this->publicDocumentUrl($workloadId));
        return 'https://view.officeapps.live.com/op/embed.aspx?src=' . $src;
    }

    public function wopiEnabled(): bool
    {
        return $this->isEnabled() && (bool) config('wordonline.wopi.enabled', false);
    }

    public function wopiEditUrl(int $workloadId, string $accessToken): ?string
    {
        if (!$this->wopiEnabled() || !$this->canUseViewer()) {
            return null;
        }
        $wopiSrc = urlencode($this->publicBaseUrl() . '/wopi/files/' . $workloadId);
        $token = urlencode($accessToken);
        $base = rtrim((string) config('wordonline.wopi.office_online_url', ''), '?');
        return $base . '?WOPISrc=' . $wopiSrc . '&access_token=' . $token;
    }

    public function downloadToken(int $workloadId, int $ttlSeconds = 86400): string
    {
        $exp = time() + $ttlSeconds;
        $secret = (string) config('wordonline.token_secret', 'ecollege_doc_secret_change_me');
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
        $secret = (string) config('wordonline.token_secret', 'ecollege_doc_secret_change_me');
        $expected = hash_hmac('sha256', $id . '|' . $exp, $secret);
        return hash_equals($expected, $sig);
    }
}
