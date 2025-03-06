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

use PrestaShop\Module\Sheepy\Constants;
use PrestaShop\Module\Sheepy\SheepyClient;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SheepyRedirectModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @return void
     *
     * @throws Exception
     */
    public function initContent(): void
    {
        parent::initContent();

        $shopName = Configuration::get('PS_SHOP_NAME');

        $cart = $this->context->cart;

        $customer = new Customer($cart->id_customer);
        $currency = new Currency($cart->id_currency);

        $link = new Link();

        $successUrl = $link->getPageLink('order-confirmation', null, null, [
            'id_cart' => $cart->id,
            'id_module' => $this->module->id,
            'key' => $customer->secure_key,
        ]);

        $data = [
            'amount' => $cart->getOrderTotal(),
            'reference' => (string) $cart->id,
            'description' => "$shopName Order #$cart->id",
            'email' => $customer->email,
            'back_url' => $this->context->link->getModuleLink('sheepy', 'cancel'),
            'success_url' => $successUrl,
            'settings' => [
                'currency' => $currency->iso_code,
                'notification_url' => $this->context->link->getModuleLink('sheepy', 'callback'),
            ],
        ];

        $sheepyService = new SheepyClient(
            Configuration::get(Constants::SHEEPY_API_KEY),
            Configuration::get(Constants::SHEEPY_API_SECRET_KEY),
            "PrestaShop Sheepy module v{$this->module->version}",
            Configuration::get(Constants::SHEEPY_PRESTASHOP_SHOP_ID)
        );

        try {
            $invoice = $sheepyService->createInvoice($data);
        } catch (Throwable $t) {
            Sheepy::logError($t->getMessage(), 'Cart', $cart->id);
        }

        if (!empty($invoice)) {
            $customer = new Customer($cart->id_customer);

            $this->module->validateOrder(
                $cart->id,
                Configuration::get(Constants::SHEEPY_STATUS_NEW),
                0,
                $this->module->displayName,
                $this->module->l('Order initialized by Sheepy'),
                null,
                (int) $currency->id,
                false,
                $customer->secure_key
            );
            Tools::redirect($invoice['data']['url']);
        } else {
            Tools::redirect('index.php?controller=order&step=3');
        }
    }
}
