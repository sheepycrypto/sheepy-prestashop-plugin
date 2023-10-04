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

use PrestaShop\Module\Sheepy\BaseFrontController;
use PrestaShop\Module\Sheepy\Constants;
use PrestaShop\Module\Sheepy\SheepyClient;

class SheepyCallbackModuleFrontController extends BaseFrontController
{
    public $ssl = true;

    private $requestTimestamp;
    private $requestSignature;

    /**
     * SheepyCallbackModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->requestTimestamp = $_SERVER[SheepyClient::TIMESTAMP_HEADER] ?? null;
        $this->requestSignature = $_SERVER[SheepyClient::SIGNATURE_HEADER] ?? null;
    }

    public function postProcess()
    {
        try {
            if (!$this->checkRequestLifetime()) {
                throw new Exception('The request timestamp is invalid or the request has expired.', 400);
            }

            if (!$this->checkRequestSignature()) {
                throw new Exception('Invalid request signature.', 400);
            }

            $requestBody = json_decode(Tools::file_get_contents('php://input'), true);

            if ($requestBody['type'] != 'invoice_status_changed') {
                $this->response();
            }

            $invoiceData = $requestBody['data'];

            $cartId = $invoiceData['invoice']['reference'];
            $orderId = Order::getIdByCartId($cartId);
            $order = new Order($orderId);

            if (!Validate::isLoadedObject($order)) {
                throw new Exception("Order #$orderId doesn't exists.", 400);
            }

            $orderStatus = Constants::ORDER_STATUS_MATCHING[$invoiceData['invoice']['status']] ?? false;

            if ($orderStatus) {
                $history = new OrderHistory();
                $history->id_order = $orderId;
                $history->changeIdOrderState(Configuration::get($orderStatus), $orderId);
                $history->add(true, ['order_name' => $orderId]);
            }
        } catch (Throwable $t) {
            $this->errorResponse($t->getMessage(), $t->getCode());
        }

        $this->response(['success' => true]);
    }

    /**
     * Check request lifetime.
     *
     * @return bool
     */
    private function checkRequestLifetime(): bool
    {
        $time = time();
        $timestamp = (int) $this->requestTimestamp;

        return $timestamp !== 0 && $timestamp <= $time && $time - $timestamp <= 5;
    }

    /**
     * Check request signature.
     *
     * @return bool
     */
    private function checkRequestSignature(): bool
    {
        $signature = SheepyClient::createSignature(
            $this->requestTimestamp,
            'POST',
            $this->context->link->getModuleLink('sheepy', 'callback'),
            Tools::file_get_contents('php://input'),
            Configuration::get(Constants::SHEEPY_NOTIFICATION_SECRET_KEY)
        );

        return !empty($this->requestSignature) && $this->requestSignature == $signature;
    }
}
