<?php
namespace App\Services;

class UrlNormalizer {
    public function normalize(string $url): string {
        $url = trim($url);

        // Add scheme if missing
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            throw new \InvalidArgumentException('Invalid URL provided.');
        }

        $scheme = strtolower($parsed['scheme'] ?? 'https');
        $host   = strtolower($parsed['host']);
        $path   = $parsed['path'] ?? '/';

        // Normalize path
        if (empty($path)) $path = '/';

        // Build normalized URL (drop query/fragment for scanning)
        return "{$scheme}://{$host}{$path}";
    }

    public function isValid(string $url): bool {
        try {
            $this->normalize($url);
            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }

    public function getDomain(string $url): string {
        return parse_url($url, PHP_URL_HOST) ?? $url;
    }

    public function isSameDomain(string $baseUrl, string $link): bool {
        $baseDomain = $this->getDomain($baseUrl);
        $linkDomain = $this->getDomain($link);
        return $baseDomain === $linkDomain;
    }

    public function resolveLink(string $base, string $href): ?string {
        if (empty($href) || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, 'javascript:')) {
            return null;
        }
        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }
        $parsed = parse_url($base);
        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host'] ?? '';
        if (str_starts_with($href, '//')) {
            return $scheme . ':' . $href;
        }
        if (str_starts_with($href, '/')) {
            return "{$scheme}://{$host}{$href}";
        }
        $basePath = dirname($parsed['path'] ?? '/');
        return "{$scheme}://{$host}{$basePath}/{$href}";
    }
}
