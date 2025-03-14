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

{block name='garantibbva_installments'}
    {if isset($installments) && !empty($installments)}
        <div class="gbbva-installment-container" data-modern-garantibbva-wrapper>
            <div class="gbbva-installment-tabs">
                <div class="gbbva-tab-header" data-modern-garantibbva-logos>
                    {foreach from=$installments item=installment key=family}
                        {if !empty($installment)}
                            <div class="gbbva-tab-item" data-modern-card-target="{$family|escape:'html':'UTF-8'}">
                                <img
                                        height="35" 
                                        src="https://cdn.paythor.com/assets/cards/{$family|escape:'html':'UTF-8'}.png"
                                        alt="{$family|escape:'html':'UTF-8'}"
                                        loading="lazy"
                                >
                            </div>
                        {/if}
                    {/foreach}
                </div>
                <div class="gbbva-tab-content" data-modern-garantibbva-tables>
                    {foreach from=$installments item=installment key=family}
                        {if !empty($installment)}
                            <div class="gbbva-tab-pane" data-modern-card-content="{$family|escape:'html':'UTF-8'}">
                                <table class="gbbva-installment-table">
                                    <thead>
                                    <tr>
                                        <th class="text-center" width="33.33%">
                                            {l s='Installment' mod='garantibbva'}
                                        </th>
                                        <th class="text-center" width="33.33%">
                                            {l s='Monthly Pay.' mod='garantibbva'}
                                        </th>
                                        <th class="text-center" width="33.33%">
                                            {l s='Total' mod='garantibbva' }
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {for $installment_count=1 to 12}
                                        {assign var="rate" value=null}
                                        {foreach from=$installment item=inst key=i}
                                            {if $i+1 == $installment_count}
                                                {assign var="rate" value=$inst}
                                            {/if}
                                        {/foreach}

                                        <tr class="text-center {if $rate !== null}modern-available{else}modern-unavailable{/if}">
                                            <td>
                                                <span class="modern-installment-count">
                                                {$installment_count|escape:'html':'UTF-8'}
                                                </span>
                                                <span class="modern-installment-text">
                                                {l s='Installment' mod='garantibbva'}
                                                </span>
                                            </td>
                                            {if $rate !== null}
                                                {if $installment_count == 1 && $rate['gateway_fee_percent'] == 0}
                                                    {assign var="total_amount" value=$price}
                                                    {assign var="monthly_payment" value=$total_amount}
                                                {else}
                                                    {assign var="total_amount" value=$price * (1 + $rate['gateway_fee_percent']/100)}
                                                    {assign var="monthly_payment" value=$total_amount/$installment_count}
                                                {/if}
                                                <td class="modern-monthly-payment">
                                                <span class="modern-price">
                                                    {$monthly_payment|escape:'html':'UTF-8'|string_format:"%.2f"} {$currencySymbol}
                                                </span>
                                                </td>
                                                <td class="modern-total-amount">
                                                <span class="modern-price">
                                                    {$total_amount|escape:'html':'UTF-8'|string_format:"%.2f"} {$currencySymbol}
                                                </span>
                                                </td>
                                            {else}
                                                <td class="modern-unavailable">-</td>
                                                <td class="modern-unavailable">-</td>
                                            {/if}
                                        </tr>
                                    {/for}
                                    </tbody>
                                </table>
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>

            <div class="gbbva-installment-note" data-modern-garantibbva-note>
                <p class="alert alert-info">
                    <i class="material-icons">info</i>
                    {l s='Installment amounts are estimated and may vary according to your bank\'s campaigns and interest rates.' mod='garantibbva'}
                </p>
            </div>
        </div>
    {/if}
{/block}

{literal}
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // İlk taksit tablosunu göster
            const firstTable = document.querySelector('[data-modern-card-content]');
            const firstLogo = document.querySelector('[data-modern-card-target]');
            if (firstTable && firstLogo) {
                firstTable.classList.add('active');
                firstLogo.classList.add('active');
            }

            // Logo tıklama olayları
            document.querySelectorAll('[data-modern-card-target]').forEach(logo => {
                logo.addEventListener('click', function() {
                    const targetCard = this.getAttribute('data-modern-card-target');

                    // Tüm active sınıflarını kaldır
                    document.querySelectorAll('[data-modern-card-target]').forEach(el => el.classList.remove('active'));
                    document.querySelectorAll('[data-modern-card-content]').forEach(el => el.classList.remove('active'));

                    // Seçili kartı active yap
                    this.classList.add('active');
                    document.querySelector(`[data-modern-card-content="${targetCard}"]`).classList.add('active');
                });
            });
        });
    </script>
{/literal}