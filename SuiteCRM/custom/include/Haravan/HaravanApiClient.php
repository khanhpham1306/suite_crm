<?php
/**
 * Haravan Integration — API Client
 *
 * Provides endpoint-specific methods that transparently handle cursor-based
 * pagination. Each method returns a PHP Generator: the caller iterates over
 * pages (arrays of records) without needing to manage pagination state.
 *
 * Pagination model (Haravan / Shopify cursor style):
 *   First request: uses updated_at_min + limit=250
 *   Subsequent requests: use page_info from Link rel="next" header ONLY
 *     (Haravan ignores updated_at_min once page_info is present)
 *   Stop when no rel="next" in Link header.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once dirname(__FILE__) . '/HaravanHttpClient.php';

class HaravanApiClient
{
    /** @var HaravanHttpClient */
    private $http;

    /** @var int  Records per page — Haravan max is 250 */
    private $pageSize = 250;

    /**
     * @param HaravanHttpClient $http
     */
    public function __construct(HaravanHttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Yield all customers updated at or after $updatedAtMin.
     *
     * @param  string $updatedAtMin  ISO-8601 datetime, e.g. "2024-01-01T00:00:00+00:00"
     * @return Generator  Each yield returns an array of customer objects (up to 250)
     */
    public function fetchCustomers($updatedAtMin)
    {
        $params = array(
            'updated_at_min' => $updatedAtMin,
            'limit'          => $this->pageSize,
        );

        return $this->paginatedFetch('customers.json', 'customers', $params);
    }

    /**
     * Yield all orders (any status) updated at or after $updatedAtMin.
     *
     * @param  string $updatedAtMin  ISO-8601 datetime
     * @return Generator  Each yield returns an array of order objects (up to 250)
     */
    public function fetchOrders($updatedAtMin)
    {
        $params = array(
            'updated_at_min' => $updatedAtMin,
            'status'         => 'any',
            'limit'          => $this->pageSize,
        );

        return $this->paginatedFetch('orders.json', 'orders', $params);
    }

    /**
     * Paginated fetch using Haravan cursor-based pagination via Link headers.
     *
     * @param  string $endpoint     Haravan admin endpoint, e.g. "customers.json"
     * @param  string $resourceKey  JSON response key, e.g. "customers"
     * @param  array  $firstParams  Query params for the first request only
     * @return Generator
     */
    private function paginatedFetch($endpoint, $resourceKey, array $firstParams)
    {
        // Use a closure to create the generator so we can return it properly
        $http       = $this->http;
        $pageSize   = $this->pageSize;

        $generator = function () use ($http, $endpoint, $resourceKey, $firstParams, $pageSize) {
            $params = $firstParams;

            while (true) {
                $GLOBALS['log']->info(
                    'HaravanApiClient: fetching ' . $endpoint .
                    ' params=' . json_encode($params)
                );

                $response = $http->get($endpoint, $params);
                $data     = json_decode($response['body'], true);

                if (!is_array($data) || !isset($data[$resourceKey])) {
                    $GLOBALS['log']->error(
                        'HaravanApiClient: unexpected response shape for ' . $endpoint .
                        ': ' . $response['body']
                    );
                    break;
                }

                $records = $data[$resourceKey];

                if (empty($records)) {
                    break;
                }

                yield $records;

                // Parse Link header for the next-page cursor
                $links = HaravanHttpClient::parseLinkHeader($response['link_header']);

                if (empty($links['next'])) {
                    // No more pages
                    break;
                }

                // Next request: use ONLY page_info (drop updated_at_min and other filters)
                parse_str($links['next'], $nextParams);
                $params = array(
                    'page_info' => $nextParams['page_info'] ?? $nextParams,
                    'limit'     => $pageSize,
                );
            }
        };

        return $generator();
    }
}
