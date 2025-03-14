<?php

/**
 * 2007-2024 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2024 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */


use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Eticsoft\Sanalpospro\InternalApi;
use Eticsoft\Sanalpospro\ApiClient;
use Eticsoft\Sanalpospro\EticConfig;
use Eticsoft\Sanalpospro\ApiResponse;
use Eticsoft\Sanalpospro\EticTools;

include _PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . 'garantibbva' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'include.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class garantibbva extends PaymentModule
{
    protected $config_form = false;


    public function __construct()
    {
        $this->name = 'garantibbva';
        $this->tab = 'payments_gateways';
        $this->version = '0.1.2';
        $this->author = 'EticSoft R&D Lab';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => '8.5'];

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Garanti BBVA Payment Gateway');
        $this->description = $this->l(
            'GarantiBBVA Payment Gateway allows you to accept payments via credit/debit cards by using GarantiBBVA Payment Services.'
        );

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall GarantiBBVA Payment Gateway?');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        // Temel ayarlar
        Configuration::updateValue('GARANTIBBVA_PUBLIC_KEY', '');
        Configuration::updateValue('GARANTIBBVA_SECRET_KEY', '');

        // Ek ayarlar için default değerler
        Configuration::updateValue('GARANTIBBVA_ORDER_STATUS', '2'); // 2 = Payment accepted
        Configuration::updateValue('GARANTIBBVA_CURRENCY_CONVERT', 'no');
        Configuration::updateValue('GARANTIBBVA_SHOWINSTALLMENTSTABS', 'no');
        Configuration::updateValue('GARANTIBBVA_PAYMENTPAGETHEME', 'classic');
        Configuration::updateValue('GARANTIBBVA_INSTALLMENTS', '[]');
        $xfvv = hash('sha256', time() . rand(1000000, 9999999));
        EticConfig::set('GARANTIBBVA_XFVV', $xfvv);

        include dirname(__FILE__) . '/sql/install.php';

        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('displayAdminOrderTop')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayProductExtraContent')
            && $this->installAdminTab();
    }

    private function installAdminTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminGarantiBBVAIapi');
        if (!$tabId) {
            $tabId = null;
        }
        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = 'AdminGarantiBBVAIapi';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'GarantiBBVA Iapi';
        }
        $tab->id_parent = -1;  // -1 means hidden tab
        $tab->module = $this->name;

        return $tab->save();
    }

    private function uninstallAdminTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminGarantiBBVAIapi');
        if ($tabId) {
            $tab = new Tab($tabId);
            return $tab->delete();
        }
        return true;
    }


    public function uninstall()
    {
        // Uninstall module-specific configuration values
        Configuration::deleteByName('GARANTIBBVA_ORDER_STATUS');
        Configuration::deleteByName('GARANTIBBVA_CURRENCY_CONVERT');
        Configuration::deleteByName('GARANTIBBVA_SHOWINSTALLMENTSTABS');
        Configuration::deleteByName('GARANTIBBVA_PAYMENTPAGETHEME');
        Configuration::deleteByName('GARANTIBBVA_INSTALLMENTS');
        Configuration::deleteByName('GARANTIBBVA_PUBLIC_KEY');
        Configuration::deleteByName('GARANTIBBVA_SECRET_KEY');

        // Additional configuration values to remove
        Configuration::deleteByName('CONF_GARANTIBBVA_FIXED');
        Configuration::deleteByName('CONF_GARANTIBBVA_VAR');
        Configuration::deleteByName('CONF_GARANTIBBVA_FIXED_FOREIGN');
        Configuration::deleteByName('CONF_GARANTIBBVA_VAR_FOREIGN');

        // Drop module-specific database tables
        $sql = [];
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'garantibbva_transaction`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'garantibbva`';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        // Unregister specific hooks
        $this->unregisterHook('paymentOptions');
        $this->unregisterHook('displayAdminOrderTop');
        $this->unregisterHook('actionFrontControllerSetMedia');
        $this->unregisterHook('displayProductExtraContent');
        $this->uninstallAdminTab();


        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $getOrderStatus = OrderState::getOrderStates($this->context->language->id);

        $this->context->smarty->assign('orderStatus', $getOrderStatus);
        $this->context->smarty->assign('iapi_base_url', $this->context->link->getAdminLink('AdminGarantiBBVAIapi'));
        $this->context->smarty->assign('iapi_xfvv', EticConfig::get('GARANTIBBVA_XFVV'));
        $this->context->smarty->assign('GARANTIBBVA_PAYMENTPAGE_THEME', EticConfig::get('GARANTIBBVA_PAYMENTPAGE_THEME'));
        $this->context->smarty->assign('GARANTIBBVA_ORDER_STATUS', EticConfig::get('GARANTIBBVA_ORDER_STATUS'));
        $this->context->smarty->assign('GARANTIBBVA_CURRENCY_CONVERT', EticConfig::get('GARANTIBBVA_CURRENCY_CONVERT'));
        $this->context->smarty->assign('GARANTIBBVA_SHOWINSTALLMENTSTABS', EticConfig::get('GARANTIBBVA_SHOWINSTALLMENTSTABS'));
        $this->context->smarty->assign('store_url', $this->context->link->getBaseLink());

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    public function hookDisplayAdminOrderTop($param)
    {
        $order = new Order((int) Tools::getValue('id_order'));
        $orderState = new OrderState((int) $order->current_state);

        if ($orderState->paid && $order->module == $this->name) {
            $this->context->smarty->assign([
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'order_total' => $order->total_paid,
                'order_currency' => new Currency($order->id_currency),
                'order_state' => $orderState->name,
            ]);

            return $this->display(__FILE__, '../admin/order.tpl');
        }

        return '';
    }


    public function hookActionFrontControllerSetMedia()
    {
        // add front.js
        $this->context->controller->registerJavascript(
            'module-GarantiBBVA-front',
            'modules/' . $this->name . '/views/js/front.js',
            ['position' => 'bottom', 'priority' => 150]
        );
        $this->context->controller->addCSS(
            'modules/' . $this->name . '/views/css/gbbva-payment.css',
            ['media' => 'all', 'priority' => 150]
        );

        //önemli
        Media::addJsDef(
            [
                'garanti_bbva_front_handler_url' => $this->context->link->getModuleLink($this->name, 'paymenthandler', [], true),
                'garanti_bbva_front_xfvv' => EticConfig::get('GARANTIBBVA_XFVV'),
            ]
        );
    }

    public function hookPaymentOptions()
    {
        if (!$this->active) {
            return [];
        }

        // add https://code.jquery.com/jquery-3.7.1.js
        $this->context->controller->registerJavascript(
            'module-GarantiBBVA-jquery',
            'https://code.jquery.com/jquery-3.7.1.js',
            ['server' => 'remote', 'position' => 'head', 'priority' => 150]
        );

        $this->context->smarty->assign([
            'ids' => [
                'id_cart' => Context::getContext()->cart->id,
                'id_customer' => Context::getContext()->customer->id,
                'id_lang' => Context::getContext()->language->id,
                'id_currency' => Context::getContext()->currency->id,
                'id_shop' => Context::getContext()->shop->id,
            ],
            'module_dir' => $this->_path,
            'payment_url' => $this->context->link->getModuleLink(
                $this->name,
                'payment',
                [],
                true
            ),
            'order_confirmation_url' => $this->context->link->getModuleLink(
                $this->name,
                'orderconfirmation',
                [],
                true
            ),
            'btn_label' => $this->l('GarantiBBVA Payment'),
        ]);

        $paymentOption = new PaymentOption();
        $paymentOption
            ->setCallToActionText($this->l('Pay with GarantiBBVA'))
            ->setAdditionalInformation(
                $this->context->smarty->fetch(
                    $this->getTemplatePath('payment_form.tpl')
                )
            )
            ->setModuleName($this->name);

        return [$paymentOption];
    }

    public function hookDisplayProductExtraContent($params)
    {
        if (Configuration::get('GARANTIBBVA_SHOWINSTALLMENTSTABS') == 'no') {
            return '';
        }

        $this->context->controller->registerStylesheet(
            'module-garantibbva-front',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all', 'priority' => 150]
        );

        $product = new Product($params['product']->id);
        $price = $product->getPrice(true, null, 2);

        // GarantiBBVA taksit verilerini alalım
        $installments = json_decode(Configuration::get('GARANTIBBVA_INSTALLMENTS') ?? '[]', true) ?? [];
        if (!empty($installments['default'])) {
            unset($installments['default']);
        }

        foreach ($installments as $key => $installment) {
            foreach ($installment as $key2 => $value) {
                if ($value['gateway'] == 'off') {
                    unset($installments[$key][$key2]);
                }
            }
        }

        $currencySymbol = Context::getContext()->currency->sign;

        $this->context->smarty->assign([
            'price' => $price,
            'installments' => $installments,
            'currencySymbol' => $currencySymbol
        ]);
        if (Configuration::get('GARANTIBBVA_PAYMENTPAGETHEME') == 'classic') {
            $content = $this->context->smarty->fetch($this->getTemplatePath('installments/classic.tpl'));
        } elseif (Configuration::get('GARANTIBBVA_PAYMENTPAGETHEME') == 'modern') {
            $content = $this->context->smarty->fetch($this->getTemplatePath('installments/modern.tpl'));
        } else {
            $content = $this->context->smarty->fetch($this->getTemplatePath('installments/classic.tpl'));
        }

        $array = [];
        $array[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
            ->setTitle($this->l('Installments', 'GarantiBBVA'))
            ->setContent($content);
        return $array;
    }

    // >= 1.7.x template path
    public function getTemplatePath($template)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return 'module:' .
                $this->name .
                '/views/templates/front/' .
                $template;
        } else {
            return $this->local_path . 'views/templates/front/' . $template;
        }
    }
}
