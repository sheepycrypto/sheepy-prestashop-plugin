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

class Constants
{
    /* Sheepy module configuration keys */
    public const SHEEPY_API_KEY = 'SHEEPY_API_KEY';
    public const SHEEPY_API_SECRET_KEY = 'SHEEPY_API_SECRET_KEY';
    public const SHEEPY_NOTIFICATION_SECRET_KEY = 'SHEEPY_NOTIFICATION_SECRET_KEY';
    public const SHEEPY_PRESTASHOP_SHOP_ID = 'SHEEPY_PRESTASHOP_SHOP_ID';
    public const SHEEPY_CONFIGURATION_KEYS = [
        self::SHEEPY_API_KEY => null,
        self::SHEEPY_API_SECRET_KEY => null,
        self::SHEEPY_NOTIFICATION_SECRET_KEY => null,
    ];

    /* Sheepy module order statuses */
    public const SHEEPY_STATUS_CONFIRMING = 'SHEEPY_STATUS_CONFIRMING';
    public const SHEEPY_STATUS_EXPIRED = 'SHEEPY_STATUS_EXPIRED';
    public const SHEEPY_STATUS_INVALID = 'SHEEPY_STATUS_INVALID';
    public const SHEEPY_STATUS_NEW = 'SHEEPY_STATUS_NEW';
    public const SHEEPY_STATUS_PARTIALLY_PAID = 'SHEEPY_STATUS_PARTIALLY_PAID';

    public const SHEEPY_STATUSES = [
        self::SHEEPY_STATUS_CONFIRMING => [
            'Awaiting Sheepy payment confirmations',
            '#34209E',
        ],
        self::SHEEPY_STATUS_EXPIRED => [
            'Sheepy payment expired',
            '#2C3E50',
        ],
        self::SHEEPY_STATUS_INVALID => [
            'Sheepy payment is invalid',
            '#E74C3C',
        ],
        self::SHEEPY_STATUS_NEW => [
            'Awaiting Sheepy payment',
            '#34209E',
        ],
        self::SHEEPY_STATUS_PARTIALLY_PAID => [
            'Sheepy partial payment received',
            '#3498D8',
        ],
    ];

    /* PrestaShop native order statuses */
    public const PS_OS_ERROR = 'PS_OS_ERROR';
    public const PS_OS_PAYMENT = 'PS_OS_PAYMENT';
    public const PS_OS_REFUND = 'PS_OS_REFUND';

    public const ORDER_STATUS_MATCHING = [
        'confirming' => self::SHEEPY_STATUS_CONFIRMING,
        'done' => self::PS_OS_PAYMENT,
        'error' => self::PS_OS_ERROR,
        'expired' => self::SHEEPY_STATUS_EXPIRED,
        'invalid' => self::SHEEPY_STATUS_INVALID,
        'new' => self::SHEEPY_STATUS_NEW,
        'partially_paid' => self::SHEEPY_STATUS_PARTIALLY_PAID,
        'refund_requested' => false,
        'refunded' => self::PS_OS_REFUND,
    ];

    /* Countries not supported by Sheepy */
    public const PROHIBITED_COUNTRIES = [
        'AF', 'AL', 'BY', 'BF', 'CF', 'CN', 'CD', 'ER', 'GU', 'GW', 'HT', 'IR', 'IQ', 'JM', 'LB', 'LY', 'ML', 'MM',
        'KP', 'PA', 'RU', 'SN', 'SO', 'SS', 'SD', 'SY', 'VI', 'UG', 'UM', 'YE',
    ];

    /* Required consents for CloudSync */
    public const REQUIRED_CONSENTS = [
        'info',         // (mandatory): The shop technical data such as the version of PrestaShop or PHP (read only)
        'modules',      // (mandatory): The list of modules installed on the shop (read only)
        'themes',       // (mandatory): The list of themes installed on the shop (read only)
//        'carts',        // Information about the shopping carts of the shop (read only)
//        'carriers',     // The characteristics of the carriers available on the shop (read only)
//        'categories',   // The list of product categories of the shop (read only)
//        'currencies',   // The list of currencies available in the shop (read only)
//        'customers',    // The anonymized list of the shop customers (read only)
//        'orders',       // Information about orders placed on the shop (read only)
//        'products',     // The list of products available on the shop (read only)
//        'stocks',       // The list of stocks and associated movements on the shop (read only)
//        'stores',       // The list of stores on the shop (read only)
//        'taxonomies',   // Advanced categories available on the shop (read only)
//        'wishlists',    // The anonymized wishlists of the customers (read only)
    ];
}
