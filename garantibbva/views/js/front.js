/**
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
*/


const SELECTORS = {
    PAYMENT_IFRAME_CONTAINER: '#payment-iframe-container',
    PAYMENT_IFRAME_CLOSE_BTN: '#payment-iframe-container-close-btn',
    PAYMENT_MODULE: '.payment-options input[data-module-name="garantibbva"]',
    PAYMENT_CONTAINER: '.payment-container',
    PAYMENT_CONFIRMATION: '#payment-confirmation'
};

class PaymentHandler {
    constructor() {
        this.iframeContainer = null;
        this.isEventListenerAdded = false;
        this.init();
    }

    init() {
        // Event delegation for dynamically created elements
        document.body.addEventListener('click', (e) => {
            if (e.target.matches(SELECTORS.PAYMENT_IFRAME_CLOSE_BTN)) {
                this.closeIframe();
            }
        });

        this.setupChangeDetection();
    }

    setupChangeDetection() {
        const handler = () => this.handlePaymentOptionChange();

        // Consolidate event listeners
        document.body.addEventListener('change', handler);
        document.addEventListener('DOMContentLoaded', handler);
        window.addEventListener('load', handler);
    }

    closeIframe() {
        const container = document.querySelector(SELECTORS.PAYMENT_IFRAME_CONTAINER);
        if (container) {
            container.remove();
        }
    }

    createPaymentButton() {
        // Önce modülümüzün seçili olup olmadığını kontrol edelim
        const garantibbvaRadio = document.querySelector('input[data-module-name="garantibbva"]');
        if (!garantibbvaRadio || !garantibbvaRadio.checked) {
            // Eğer modülümüz seçili değilse buton oluşturmayalım
            const existingButton = document.querySelector('#garantibbva-payment-button');
            if (existingButton) {
                existingButton.remove();
            }
            return;
        }

        // Eğer buton zaten varsa yeni oluşturmayalım
        if (!document.querySelector('#garantibbva-payment-button')) {
            const button = document.createElement('button');
            Object.assign(button, {
                id: 'garantibbva-payment-button',
                type: 'submit',
                innerText: btnLabel,
                className: 'btn btn-primary'
            });

            button.addEventListener('click', () => this.handlePaymentRequest());
            return button;
        }
    }

    async handlePaymentRequest() {
        try {
            $.ajax({
                url: garanti_bbva_front_handler_url,
                type: 'POST',
                data: {
                    iapi_action: 'createPaymentLink',
                    iapi_xfvv: garanti_bbva_front_xfvv,
                    iapi_params: ''
                },
                success: (response) => {
                    this.handlePaymentResponse(response);
                },
                error: (error) => {
                    console.error('Payment request failed:', error);
                }
            });
        } catch (error) {
            console.error('Payment request failed:', error);
        }
    }

    handlePaymentResponse(response) {
        if (response.status === "success" && response.data.payment_link) {
            this.showPaymentIframe(response.data.payment_link);
        } else {
            this.showError(response.message);
        }
    }

    showPaymentIframe(paymentLink) {
        const iframeHTML = `
            <div id="payment-iframe-container" class="gbbva-iframe-container">
                <div class="gbbva-iframe-wrapper">
                    <div class="gbbva-iframe-header">
                    <button type="button" class="gbbva-close-iframe" onclick="document.getElementById('payment-iframe-container').remove()">
                        ×            
                    </button>
                    </div>
                    <div class="gbbva-iframe-content">
                        <iframe src="${paymentLink}" class="gbbva-payment-iframe"></iframe>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', iframeHTML);
    }

    showError(errorMessage) {
        const errorHTML = `
            <div class="alert alert-danger">
                ${errorMessage}
            </div>
        `;

        document.querySelector(SELECTORS.PAYMENT_CONTAINER).insertAdjacentHTML('beforeend', errorHTML);
    }

    handlePaymentOptionChange() {
        const isChecked = document.querySelector(SELECTORS.PAYMENT_MODULE)?.checked;

        // Mevcut ödeme butonunu kontrol et ve kaldır
        const existingButton = document.querySelector('#garantibbva-payment-button');
        if (existingButton) {
            existingButton.remove();
        }

        const confirmButton = document.querySelector(prestashop.selectors.checkout.confirmationSelector);
        
        // Önce eski event listener'ı kaldıralım
        if (confirmButton) {
            confirmButton.removeEventListener('click', this.handlePaymentButtonClick.bind(this));
        }

        if (isChecked && confirmButton && !this.isEventListenerAdded) {
            // Event listener'ı sadece bir kez ekleyelim
            this.isEventListenerAdded = true;
            confirmButton.addEventListener('click', this.handlePaymentButtonClick.bind(this));
        } else if (!isChecked) {
            // Modül seçili değilse flag'i sıfırlayalım
            this.isEventListenerAdded = false;
        }
    }

    handlePaymentButtonClick(e) {
        e.preventDefault();
        e.stopPropagation();
        this.handlePaymentRequest();
    }
}

// Initialize the payment handler
const paythorePayment = new PaymentHandler();

// Listen for post messages from PayThor
window.addEventListener('message', function (event) {
    if (event.origin === 'https://pay.paythor.com' || event.origin === 'https://dev-pay.paythor.com') {
        // Response kontrolü
        if (event.data && event.data.isSuccess === true && event.data.processID) {
            try {
                // İframe kapatma butonunu devre dışı bırak
                const closeButton = document.querySelector('.gbbva-close-iframe');
                if (closeButton) {
                    closeButton.disabled = true;
                }
                // ESC tuşunu ve yenileme tuşlarını devre dışı bırak
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' || e.key === 'F5' || e.keyCode === 27) {
                        e.preventDefault();
                        return false;
                    }
                });

                window.onbeforeunload = function() {
                    return "Ödeme işlemi devam ediyor.";
                };

                $.ajax({
                    url: garanti_bbva_front_handler_url,
                    type: 'POST',
                    data: {
                        iapi_action: 'confirmOrder',
                        iapi_xfvv: garanti_bbva_front_xfvv,
                        iapi_params: JSON.stringify({ process_token: event.data.processID })
                    },
                    success: (response) => {
                        if (response.data.redirect_url) {
                            console.log("Redirecting to:", response.data.redirect_url);
                            setTimeout(() => {
                                window.onbeforeunload = null;
                                window.location.href = response.data.redirect_url;
                            }, 500);
                        }
                    },
                    error: (error) => {
                        window.onbeforeunload = null;
                        console.error('Payment request failed:', error);
                    }
                });
            } catch (error) {
                console.error('Payment request failed:', error);
            }
        }
    }
}, false);

// Add styles for the iframe overlay
const styles = `
    .gbbva-iframe-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .gbbva-iframe-wrapper { 
        position: relative;
        width: 90%;
        max-width: 900px;
        height: 80vh;
        max-height: 70%;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .gbbva-payment-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .gbbva-iframe-content {
        flex: 1;
        position: relative;
    }

    .gbbva-iframe-header {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 10px 15px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .gbbva-close-iframe {
        background: none;
        border: none;
        font-size: 28px;
        line-height: 1;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        padding: 20px;
        border: 1px solid transparent;
        border-radius: .25rem;
    }
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = styles;
document.head.appendChild(styleSheet);