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


<div class="payment-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script>
    // Mevcut değişkenler
    const btnLabel = "{$btn_label|escape:'javascript':'UTF-8'}";
    const id_cart = "{$ids.id_cart|escape:'javascript':'UTF-8'}";

    // Logo URL'sini tanımlayın
    const logoUrl = "https://www.garantibbva.com.tr/content/experience-fragments/public-website/tr/site/header/master/_jcr_content/root/header/headermobile/image.coreimg.svg/1699885503269/logo.svg"; // Logonuzun URL'si

    // Logoyu dinamik olarak ekleme
    $(document).ready(function() {
        // Logo img elementi oluştur
        const logoImg = $('<img>', {
            src: logoUrl,
            alt: 'Site Logo',
            class: 'payment-logo'
        });

        // Logoyu payment-container div'ine ekle
        $('.payment-container').prepend(logoImg);
    });
</script>

