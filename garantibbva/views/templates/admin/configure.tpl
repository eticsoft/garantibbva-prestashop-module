{*
* 2007-2025 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*}


<script>
  const current_version = 1.7;
  
  const iapi_base_url = '{$iapi_base_url}';
  const store_url = '{$store_url}';
  const iapi_xfvv = '{$iapi_xfvv}';

  let generalSettings = {
    update_version: 8.0 > current_version,
    order_status: {
      default_value: "{$GARANTIBBVA_ORDER_STATUS|default:'2'|escape:'html':'UTF-8'}",
      options: {
          {foreach from=$orderStatus item=order_state}
            {$order_state.id_order_state|escape:'html':'UTF-8'}: '{$order_state.name|escape:'html':'UTF-8'}'{if !$order_state@last},{/if}
          {/foreach}
      }
    },
    currency_convert: {
      default_value: '{$GARANTIBBVA_CURRENCY_CONVERT|default:'no'|escape:'html':'UTF-8'}',
      options: {
        yes: "{l s='Yes' mod='garantibbva'}",
        no: "{l s='No' mod='garantibbva'}"
      }
    },
    showInstallmentsTabs: {
      default_value: '{$GARANTIBBVA_SHOWINSTALLMENTSTABS|default:'yes'|escape:'html':'UTF-8'}',
      options: {
        yes: "{l s='Yes' mod='garantibbva'}",
        no: "{l s='No' mod='garantibbva'}"
      }
    },
    paymentPageTheme: {
      default_value: '{$GARANTIBBVA_PAYMENTPAGETHEME|default:'modern'|escape:'html':'UTF-8'}',
      options: {
        classic: "{l s='Classic' mod='garantibbva'}",
        modern: "{l s='Modern' mod='garantibbva'}"
      }
    }
  }
</script>
<script type="module" src="https://cdn.paythor.com/3/102/0.1.2/index.js"></script>
<link rel="stylesheet" href="https://cdn.paythor.com/3/102/0.1.2/index.css">
<div id="paythor-container" 
     data-platform="prestashop" 
     data-app-id="102"
     data-program-id="3"
     >
</div>
