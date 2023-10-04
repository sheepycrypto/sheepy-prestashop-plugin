<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Sheepy;

class SheepyClient
{
    /* @todo Replace API URL */
    private $apiUrl = 'https://sheepy-api-release.cpay.io';
    private $apiKey;
    private $secretKey;
    private $userAgent;

    public const TIMESTAMP_HEADER = 'HTTP_X_TIMESTAMP';
    public const SIGNATURE_HEADER = 'HTTP_X_SIGNATURE';

    /**
     * SheepyClient constructor.
     *
     * @param string $apiKey
     * @param string $secretKey
     * @param string $userAgent
     */
    public function __construct(string $apiKey, string $secretKey, string $userAgent)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->userAgent = $userAgent;
    }

    /**
     * Create invoice.
     *
     * @param array $bodyParams
     *
     * @return array
     *
     * @throws \Exception
     */
    public function createInvoice(array $bodyParams): array
    {
        return $this->request('POST', '/invoices', $bodyParams);
    }

    /**
     * Send a request.
     *
     * @param string $method
     * @param string $path
     * @param array $bodyParams
     * @param array $urlParams
     *
     * @return array
     *
     * @throws \Exception
     */
    private function request(string $method, string $path, array $bodyParams = [], array $urlParams = []): array
    {
        $url = $this->apiUrl . '/api/v1' . $path . (!empty($urlParams) ? '?' . http_build_query($urlParams) : '');
        $time = time();
        $signature = self::createSignature($time, $method, $url, json_encode($bodyParams), $this->secretKey);

        $headers = [
            'Accept: application/json',
            'Content-type: application/json',
            "User-Agent: $this->userAgent",
            "X-Token: $this->apiKey",
            "X-Signature: $signature",
            "X-Timestamp: $time",
        ];

        $curl = curl_init();

        curl_setopt_array($curl, $this->getCurlOption($method, $url, $headers, $bodyParams));

        $responseBody = curl_exec($curl);
        $responseBody = json_decode($responseBody, true);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($responseBody === false) {
            throw new \Exception('Request error: ' . curl_error($curl));
        }

        if ($responseCode !== 200) {
            throw new \Exception('Request error: ' . $responseBody['message']);
        }

        curl_close($curl);

        return $responseBody;
    }

    /**
     * Get CURL options.
     *
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param array $params
     *
     * @return array
     */
    private function getCurlOption(string $method, string $url, array $headers = [], array $params = []): array
    {
        switch ($method) {
            case 'GET':
                $url .= '?' . http_build_query($params);
                break;
            case 'POST':
                $options[CURLOPT_POSTFIELDS] = json_encode($params);
                break;
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $options[CURLOPT_POSTFIELDS] = json_encode($params);
                break;
            case 'PATCH':
                $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
                $options[CURLOPT_POSTFIELDS] = json_encode($params);
                break;
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $url .= '?' . http_build_query($params);
                break;
        }

        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_RETURNTRANSFER] = true;

        $options[CURLOPT_TIMEOUT] = 30;
        $options[CURLOPT_CONNECTTIMEOUT] = 10;

        $options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        $options[CURLOPT_SSL_VERIFYPEER] = true;

        return $options;
    }

    /**
     * Create signature.
     *
     * @param int $time
     * @param string $method
     * @param string $url
     * @param string $body
     * @param string $key
     *
     * @return string
     */
    public static function createSignature(int $time, string $method, string $url, string $body, string $key): string
    {
        return hash_hmac('sha256', $time . strtoupper($method) . $url . $body, $key);
    }
}
