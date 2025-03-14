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

<div data-garantibbva-wrapper>
    <div data-garantibbva-container>
        {foreach from=$installments item=installment key=family}
            {* En az 1 taksit varsa kartı göster *}
            {if !empty($installment)}
                <div data-garantibbva-card>
                    <table data-garantibbva-table>
                        <thead>
                        <tr>
                            <td colspan="3" style="text-align: center;">
                                <img src="https://cdn.paythor.com/assets/cards/{$family|escape:'html':'UTF-8'}.png" alt="{$family|escape:'html':'UTF-8'}">
                            </td>
                        </tr>
                        <tr>
                            <td width="33.33%">{l s='Installment' mod='garantibbva'}</td>
                            <td width="33.33%">{l s='Monthly Pay' mod='garantibbva'}</td>
                            <td width="33.33%">{l s='Total' mod='garantibbva'}</td>
                        </tr>
                        </thead>
                        <tbody>
                        {for $installment_count=1 to 12}
                            {* Varolan taksit oranını kontrol et *}
                            {assign var="rate" value=null}
                            {foreach from=$installment item=inst key=i}
                                {if $i+1 == $installment_count}
                                    {assign var="rate" value=$inst}
                                {/if}
                            {/foreach}

                            <tr>
                                <td>{$installment_count|escape:'html':'UTF-8'} {l s='Installment' mod='garantibbva'}</td>
                                {if $rate !== null}
                                    {* Taksit hesaplama mantığı *}
                                    {if $installment_count == 1 && $rate['gateway_fee_percent'] == 0}
                                        {assign var="total_amount" value=$price}
                                        {assign var="monthly_payment" value=$total_amount}
                                    {else}
                                        {assign var="total_amount" value=$price * (1 + $rate['gateway_fee_percent']/100)}
                                        {assign var="monthly_payment" value=$total_amount/$installment_count}
                                    {/if}
                                    <td>{$monthly_payment|escape:'html':'UTF-8'|string_format:"%.2f"} {$currencySymbol}</td>
                                    <td>{$total_amount|escape:'html':'UTF-8'|string_format:"%.2f"} {$currencySymbol}</td>
                                {else}
                                    <td>-</td>
                                    <td>-</td>
                                {/if}
                            </tr>
                        {/for}
                        </tbody>
                    </table>
                </div>
            {/if}
        {/foreach}
    </div>
    <div class="data-garantibbva-card" data-modern-garantibbva-note>
        <p class="alert alert-info">
            <i class="material-icons">info</i>
            {l s='Installment amounts are estimated and may vary according to your bank\'s campaigns and interest rates.' mod='garantibbva'}
        </p>
    </div>
</div>