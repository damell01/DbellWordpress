<?php
namespace App\Services;

/**
 * ScreenshotService
 *
 * Builds a screenshot URL for a given website using a configurable
 * free screenshot API.  No API key is required for the default provider
 * (WordPress mshots), making it suitable for shared hosting on Hostinger.
 *
 * Supported providers (set via admin Settings → screenshot_provider):
 *   • mshots   – https://s.wordpress.com/mshots/v1/{url}  (default, free)
 *   • thum_io  – https://image.thum.io/get/width/1200/{url}  (free tier)
 *   • custom   – use screenshot_api_url setting as a template with {url}
 *
 * The service can also optionally *verify* that the remote image is
 * accessible before returning the URL (set screenshot_verify = 1 in
 * settings), so that broken thumbnails never appear in reports.
 */
class ScreenshotService
{
    private string $provider;
    private string $customTpl;
    private bool   $verify;

    public function __construct(string $provider = 'mshots', string $customTpl = '', bool $verify = false)
    {
        $this->provider  = $provider ?: 'mshots';
        $this->customTpl = $customTpl;
        $this->verify    = $verify;
    }

    /**
     * Return a publicly accessible screenshot URL for the given website URL.
     * Returns null when the screenshot cannot be obtained.
     */
    public function getScreenshotUrl(string $websiteUrl): ?string
    {
        if (empty($websiteUrl)) {
            return null;
        }

        $url = $this->buildUrl($websiteUrl);
        if (!$url) {
            return null;
        }

        if ($this->verify && !$this->remoteExists($url)) {
            return null;
        }

        return $url;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildUrl(string $websiteUrl): ?string
    {
        $encoded = rawurlencode($websiteUrl);

        switch ($this->provider) {
            case 'thum_io':
                return "https://image.thum.io/get/width/1200/crop/900/{$encoded}";

            case 'custom':
                if (empty($this->customTpl)) {
                    return null;
                }
                return str_replace('{url}', $encoded, $this->customTpl);

            case 'mshots':
            default:
                // mshots returns a redirect to a cached PNG; width=1200
                return "https://s.wordpress.com/mshots/v1/{$encoded}?w=1200&h=630";
        }
    }

    /**
     * Issue a lightweight HEAD request to confirm the image is reachable.
     */
    private function remoteExists(string $url): bool
    {
        if (!function_exists('curl_init')) {
            return true; // cannot verify, assume ok
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_USERAGENT      => 'SiteScope/1.0',
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $code >= 200 && $code < 400;
    }
}
