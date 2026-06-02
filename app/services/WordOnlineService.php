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
        $oo = new OnlyOfficeService();
        $token = urlencode($oo->downloadToken($workloadId));
        return $this->publicBaseUrl() . '/documents/workload/' . $workloadId . '?token=' . $token;
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
}
