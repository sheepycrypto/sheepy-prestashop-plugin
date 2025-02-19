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
require_once __DIR__ . '/vendor/autoload.php';

use PrestaShop\Module\Sheepy\Constants;
use Prestashop\ModuleLibMboInstaller\Installer;
use Prestashop\ModuleLibMboInstaller\Presenter;
use PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Sheepy extends PaymentModule
{
    /** @var ServiceContainer */
    private $container;

    /**
     * Sheepy module constructor.
     */
    public function __construct()
    {
        $this->name = 'sheepy';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.2';
        $this->author = 'Sheepy.com';
        $this->module_key = '725815802df89ed906fda462f3e114b7';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Sheepy module');
        $this->description = $this->l('Accept Bitcoin and other cryptocurrencies from anywhere in the world, and attract new customers who prefer crypto payments with Sheepy plugin.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        if (is_null($this->container)) {
            $this->container = new ServiceContainer($this->name, $this->local_path);
        }
    }

    /**
     * Install module.
     *
     * @return bool
     */
    public function install(): bool
    {
        $mboStatus = (new Presenter())->present();

        if (!$mboStatus['isInstalled']) {
            try {
                $mboInstaller = new Installer(_PS_VERSION_);

                $mboInstaller->installModule();

                $this->installDependencies();
            } catch (Throwable $t) {
                self::logError($t->getMessage());

                return false;
            }
        } else {
            $this->installDependencies();
        }

        return parent::install()
            && $this->installOrderStatuses()
            && $this->installConfiguration()
            && $this->disableRestrictedCountries()
            && $this->registerHook('paymentOptions');
    }

    /**
     * Uninstall module.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        return parent::uninstall()
            && $this->uninstallOrderStatuses()
            && $this->uninstallConfiguration();
    }

    /**
     * Handle the module configuration page.
     *
     * @return string
     */
    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $this->postValidate();

            if (empty($this->context->controller->errors)) {
                $this->postProcess();
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        $this->context->smarty->assign('modulePath', $this->_path);

        $moduleManager = ModuleManagerBuilder::getInstance()->build();

        try {
            $accountsFacade = $this->getService("$this->name.ps_accounts_facade");
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (InstallerException $e) {
            $accountsInstaller = $this->getService("$this->name.ps_accounts_installer");
            $accountsInstaller->install();
            $accountsFacade = $this->getService("$this->name.ps_accounts_facade");
            $accountsService = $accountsFacade->getPsAccountsService();
        }

        try {
            Media::addJsDef(['contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()->present($this->name)]);

            $this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());

            if ($moduleManager->isInstalled('ps_eventbus')) {
                $eventbusModule = Module::getInstanceByName('ps_eventbus');
                if (version_compare($eventbusModule->version, '1.9.0', '>=')) {
                    $eventbusPresenterService = $eventbusModule
                        ->getService('PrestaShop\Module\PsEventbus\Service\PresenterService');

                    $this->context->smarty->assign('urlCloudsync', 'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js');

                    Media::addJsDef(['contextPsEventbus' => $eventbusPresenterService->expose($this, Constants::REQUIRED_CONSENTS)]);
                }
            }

            $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
            $output .= $this->renderConfigForm();
        } catch (Throwable $t) {
            $this->context->controller->errors[] = $t->getMessage();

            return '';
        }

        return $output;
    }

    /**
     * Builds the configuration form.
     *
     * @return string
     */
    public function renderConfigForm(): string
    {
        $helper = new HelperForm();

        $helper->table = $this->table;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;
        $helper->default_form_language = $this->context->language->id;

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Add payment option at the checkout in the front office.
     *
     * @param array $parameters
     *
     * @return array
     */
    public function hookPaymentOptions(array $parameters): array
    {
        if (!$this->active) {
            return [];
        }

        if (!$this->checkCurrency($parameters['cart'])) {
            return [];
        }

        $option = new PaymentOption();

        $option
            ->setModuleName($this->name)
            ->setCallToActionText($this->l('Cryptocurrency, stablecoins and other digital assets'))
            ->setAdditionalInformation($this->fetch('module:sheepy/views/templates/hook/payment-info.tpl'))
            ->setAction($this->context->link->getModuleLink($this->name, 'redirect', [], true));

        return [$option];
    }

    /**
     * Install PrestaShop Integration Framework components.
     *
     * @return bool
     */
    private function installDependencies(): bool
    {
        $mboStatus = (new Presenter())->present();

        if (!$mboStatus['isInstalled']) {
            try {
                $mboInstaller = new Installer(_PS_VERSION_);
                $mboInstaller->installModule();
            } catch (Throwable $t) {
                self::logError($t->getMessage());

                return false;
            }
        }

        $moduleManager = ModuleManagerBuilder::getInstance()->build();

        try {
            /* PS Account */
            if (!$moduleManager->isInstalled('ps_accounts')) {
                $moduleManager->install('ps_accounts');
            } elseif (!$moduleManager->isEnabled('ps_accounts')) {
                $moduleManager->enable('ps_accounts');
                $moduleManager->upgrade('ps_accounts');
            } else {
                $moduleManager->upgrade('ps_accounts');
            }

            /* Cloud Sync - PS Eventbus */
            if (!$moduleManager->isInstalled('ps_eventbus')) {
                $moduleManager->install('ps_eventbus');
            } elseif (!$moduleManager->isEnabled('ps_eventbus')) {
                $moduleManager->enable('ps_eventbus');
                $moduleManager->upgrade('ps_eventbus');
            } else {
                $moduleManager->upgrade('ps_eventbus');
            }
        } catch (Throwable $t) {
            self::logError($t->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Install order statuses.
     *
     * @return bool
     */
    private function installOrderStatuses(): bool
    {
        $result = true;

        foreach (Constants::SHEEPY_STATUSES as $key => $value) {
            list($statusName, $statusColor) = $value;

            $status = new OrderState();

            $status->name = array_fill(0, 10, $statusName);
            $status->module_name = $this->name;
            $status->color = $statusColor;

            $status->send_email = false;
            $status->hidden = false;
            $status->delivery = false;
            $status->logable = false;
            $status->invoice = false;
            $status->paid = false;

            try {
                if ($status->add()) {
                    copy(
                        dirname(__FILE__) . '/logo.gif',
                        _PS_ROOT_DIR_ . '/img/os/' . (int) $status->id . '.gif'
                    );
                }

                $result = $result && Configuration::updateValue($key, $status->id);
            } catch (Throwable $t) {
                self::logError($t->getMessage());

                return false;
            }
        }

        return $result;
    }

    /**
     * Uninstall order statuses.
     *
     * @return bool
     */
    private function uninstallOrderStatuses(): bool
    {
        $result = true;

        foreach (array_keys(Constants::SHEEPY_STATUSES) as $status) {
            try {
                $status = new OrderState(Configuration::get($status));
                $result = $result && $status->delete();
            } catch (Throwable $t) {
                self::logError($t->getMessage());

                return false;
            }
        }

        return $result;
    }

    /**
     * Install configuration.
     *
     * @return bool
     */
    private function installConfiguration(): bool
    {
        $result = true;

        foreach (Constants::CONFIGURATION_KEYS as $key => $value) {
            $result = $result && Configuration::updateValue($key, $value);
        }

        return $result;
    }

    /**
     * Uninstall configuration.
     *
     * @return bool
     */
    private function uninstallConfiguration(): bool
    {
        $result = true;

        $configurationKeys = array_merge(
            array_keys(Constants::CONFIGURATION_KEYS),
            array_keys(Constants::SHEEPY_STATUSES)
        );

        foreach ($configurationKeys as $key) {
            $result = $result && Configuration::deleteByName($key);
        }

        return $result;
    }

    /**
     * Disable restricted countries.
     *
     * @return bool
     */
    private function disableRestrictedCountries(): bool
    {
        try {
            $db = Db::getInstance();

            $countries = $db->executeS('SELECT id_country, iso_code FROM ' . _DB_PREFIX_ . 'country');
            $countries = array_column($countries, 'id_country', 'iso_code');

            $activeCountries = Configuration::get('PS_ALLOWED_COUNTRIES');
            $activeCountries = explode(';', $activeCountries);

            $allowedCountries = [];
            $insertData = [];

            foreach ($activeCountries as $country) {
                if (!in_array($country, Constants::PROHIBITED_COUNTRIES)) {
                    $allowedCountries[] = $country;
                }
            }

            foreach ($allowedCountries as $country) {
                if (isset($countries[$country])) {
                    $insertData[] = [
                        'id_country' => (int) $countries[$country],
                        'id_module' => $this->id,
                    ];
                }
            }

            return $db->insert(
                'module_country',
                $insertData,
                false,
                true,
                Db::INSERT_IGNORE
            );
        } catch (Throwable $t) {
            self::logError($t->getMessage());

            return false;
        }
    }

    /**
     * Retrieve the service.
     *
     * @param string $serviceName
     *
     * @return object|null
     */
    public function getService(string $serviceName)
    {
        return $this->container->getService($serviceName);
    }

    /**
     * Validate configuration field values.
     *
     * @return void
     */
    private function postValidate(): void
    {
        if (empty(Tools::getValue(Constants::SHEEPY_API_KEY))) {
            $this->context->controller->errors[] = $this->l('Merchant API key is required.');
        }

        if (empty(Tools::getValue(Constants::SHEEPY_API_SECRET_KEY))) {
            $this->context->controller->errors[] = $this->l('Merchant API secret key is required.');
        }

        if (empty(Tools::getValue(Constants::SHEEPY_NOTIFICATION_SECRET_KEY))) {
            $this->context->controller->errors[] = $this->l('Notification secret key is required.');
        }

        /* @todo Test API connection here */
    }

    /**
     * Update configuration field values.
     *
     * @return void
     */
    private function postProcess(): void
    {
        Configuration::updateValue(
            Constants::SHEEPY_API_KEY,
            Tools::getValue(Constants::SHEEPY_API_KEY)
        );

        Configuration::updateValue(
            Constants::SHEEPY_API_SECRET_KEY,
            Tools::getValue(Constants::SHEEPY_API_SECRET_KEY)
        );

        Configuration::updateValue(
            Constants::SHEEPY_NOTIFICATION_SECRET_KEY,
            Tools::getValue(Constants::SHEEPY_NOTIFICATION_SECRET_KEY)
        );
    }

    /**
     * Get configuration field values.
     *
     * @return array
     */
    private function getConfigValues(): array
    {
        return [
            Constants::SHEEPY_API_KEY => Tools::getValue(
                Constants::SHEEPY_API_KEY,
                Configuration::get(Constants::SHEEPY_API_KEY)
            ),
            Constants::SHEEPY_API_SECRET_KEY => Tools::getValue(
                Constants::SHEEPY_API_SECRET_KEY,
                Configuration::get(Constants::SHEEPY_API_SECRET_KEY)
            ),
            Constants::SHEEPY_NOTIFICATION_SECRET_KEY => Tools::getValue(
                Constants::SHEEPY_NOTIFICATION_SECRET_KEY,
                Configuration::get(Constants::SHEEPY_NOTIFICATION_SECRET_KEY)
            ),
        ];
    }

    /**
     * Get configuration form.
     *
     * @return array[]
     */
    private function getConfigForm(): array
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('API Key'),
                        'name' => Constants::SHEEPY_API_KEY,
                        'prefix' => '<i class="icon icon-eye"></i>',
                        'desc' => $this->l('Enter a valid API key.'),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('API Secret Key'),
                        'name' => Constants::SHEEPY_API_SECRET_KEY,
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter a valid API secret key.'),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Notification Secret Key'),
                        'name' => Constants::SHEEPY_NOTIFICATION_SECRET_KEY,
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter a valid notification secret key.'),
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Check if the module can process to a payment with the current currency.
     *
     * @param Cart $cart
     *
     * @return bool
     */
    private function checkCurrency(Cart $cart): bool
    {
        $orderCurrency = new Currency($cart->id_currency);
        $moduleCurrencies = $this->getCurrency($cart->id_currency);

        if (is_array($moduleCurrencies)) {
            foreach ($moduleCurrencies as $moduleCurrency) {
                if ($orderCurrency->id == $moduleCurrency['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Error logging.
     *
     * @param string $message
     * @param string|null $objectType
     * @param int|null $objectId
     *
     * @return void
     */
    public static function logError(string $message, string $objectType = null, int $objectId = null): void
    {
        PrestaShopLogger::addLog($message, 3, null, $objectType, $objectId, true);
    }
}
