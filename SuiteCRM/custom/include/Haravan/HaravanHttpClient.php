<?php
/**
 * Haravan Integration — HTTP Client
 *
 * Wraps cURL to send authenticated GET requests to the Haravan REST API.
 * SuiteCRM's built-in SugarHttpClient only supports POST, so this is a
 * purpose-built replacement for GET-based API polling.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class HaravanHttpClient
{
    /** @var string  e.g. "myshop.haravan.com" */
    private $shopDomain;

    /** @var string  Private app access token */
    private $accessToken;

    /** @var int  cURL timeout in seconds */
    private $timeoutSeconds = 30;

    /**
     * @param string $shopDomain   Shop domain, e.g. "myshop.haravan.com"
     * @param string $accessToken  Haravan private-app access token
     */
    public function __construct($shopDomain, $accessToken)
    {
        $this->shopDomain  = rtrim($shopDomain, '/');
        $this->accessToken = $accessToken;
    }

    /**
     * Send a GET request to the Haravan Admin API.
     *
     * @param  string $endpoint     Path under /admin/, e.g. "customers.json"
     * @param  array  $queryParams  Query-string key-value pairs
     * @return array  ['body' => string, 'link_header' => string, 'http_code' => int]
     * @throws RuntimeException  on cURL error or non-2xx HTTP status
     */
    public function get($endpoint, array $queryParams = [])
    {
        $url = 'https://' . $this->shopDomain . '/admin/' . ltrim($endpoint, '/');

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeoutSeconds,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => array(
                'X-Haravan-Access-Token: ' . $this->accessToken,
                'Content-Type: application/json',
                'Accept: application/json',
            ),
            // Capture response headers
            CURLOPT_HEADER         => true,
        ));

        $rawResponse = curl_exec($ch);
        $curlError   = curl_error($ch);
        $httpCode    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize  = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($rawResponse === false || !empty($curlError)) {
            $msg = 'HaravanHttpClient: cURL error for ' . $url . ': ' . $curlError;
            $GLOBALS['log']->error($msg);
            throw new RuntimeException($msg);
        }

        $headerStr = substr($rawResponse, 0, $headerSize);
        $body      = substr($rawResponse, $headerSize);

        if ($httpCode < 200 || $httpCode >= 300) {
            $msg = 'HaravanHttpClient: HTTP ' . $httpCode . ' for ' . $url . ' — ' . $body;
            $GLOBALS['log']->error($msg);
            throw new RuntimeException($msg);
        }

        return array(
            'body'        => $body,
            'link_header' => $headerStr,
            'http_code'   => $httpCode,
        );
    }

    /**
     * Parse the Haravan / Shopify-style Link pagination header.
     *
     * Example header block:
     *   Link: <https://shop.haravan.com/admin/customers.json?page_info=abc&limit=250>; rel="next",
     *         <https://shop.haravan.com/admin/customers.json?page_info=xyz&limit=250>; rel="previous"
     *
     * @param  string $rawHeaders  Full raw response headers string
     * @return array  ['next' => 'page_info=abc&limit=250', 'previous' => '...']  (keys absent when not present)
     */
    public static function parseLinkHeader($rawHeaders)
    {
        $result = array();

        // Extract the Link header line
        if (!preg_match('/^Link:\s*(.+)$/mi', $rawHeaders, $lineMatch)) {
            return $result;
        }

        $linkLine = $lineMatch[1];

        // Split by comma (multiple rel values)
        $parts = preg_split('/,\s*(?=<)/', $linkLine);

        foreach ($parts as $part) {
            // Match: <URL>; rel="TYPE"
            if (!preg_match('/<([^>]+)>;\s*rel=["\']([^"\']+)["\']/', trim($part), $m)) {
                continue;
            }
            $linkUrl = $m[1];
            $rel     = $m[2];

            // Extract the query string from the URL
            $parsed = parse_url($linkUrl);
            if (!empty($parsed['query'])) {
                $result[$rel] = $parsed['query'];
            }
        }

        return $result;
    }
}
