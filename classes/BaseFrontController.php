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

if (!defined('_PS_VERSION_')) {
    exit;
}

class BaseFrontController extends \ModuleFrontController
{
    /**
     * Response.
     *
     * @param array $body
     * @param int $httpCode
     */
    protected function response(array $body = [], int $httpCode = 200): void
    {
        $this->setHeaders();

        http_response_code($httpCode);

        echo json_encode($body, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Error response.
     *
     * @param string $errorMessage
     * @param int $httpCode
     *
     * @return void
     */
    protected function errorResponse(string $errorMessage = '', int $httpCode = 400): void
    {
        $this->setHeaders();

        http_response_code($httpCode);

        echo json_encode(['error' => $errorMessage], JSON_UNESCAPED_SLASHES);

        exit;
    }

    /**
     * Set HTTP headers.
     *
     * @return void
     */
    private function setHeaders(): void
    {
        ob_end_clean();

        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/json;charset=utf-8');
        header('X-Robots-Tag: noindex, nofollow');
    }
}
