<?php

namespace Eticsoft\Sanalpospro;

use Eticsoft\Sanalpospro\EticContext;
use Eticsoft\Sanalpospro\EticConfig;
use Eticsoft\Sanalpospro\EticTools;
use Eticsoft\Sanalpospro\Payment;
use Eticsoft\Sanalpospro\ApiClient;

use Eticsoft\Common\Models\Cart;
use Eticsoft\Common\Models\Payer;
use Eticsoft\Common\Models\Order;
use Eticsoft\Common\Models\Invoice;
use Eticsoft\Common\Models\Address;
use Eticsoft\Common\Models\Shipping;
use Eticsoft\Common\Models\PaymentRequest;
use Eticsoft\Common\Models\PaymentModel;
use Eticsoft\Common\Models\CartItem;



class InternalApi
{

    public ?string $action = '';
    public ?string $payload = '';
    public ?array $params = [];
    public ?array $response = [
        'status' => 'error',
        'message' => 'Internal error',
        'data' => [],
        'xfvv' => '',

    ];

    public $module;

    public function run(): self
    {
        $this->setAction()->setParams()->call();
        return $this;
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public function setAction(): self
    {
        $this->action = EticTools::postVal('iapi_action', false);
        return $this;
    }

    public function setParams(): self
    {
        $params = EticTools::postVal('iapi_params', '');
        $this->params = json_decode($params, true);
        return $this;
    }

    public function setModule($module): self
    {
        $this->module = $module;
        return $this;
    }

    public function call(): self
    {
        if (!$this->action) {
            return $this->setResponse('error', 'Action not found. #' . $this->action);
        }
        //make action first letter uppercase
        $this->action = ucfirst($this->action);
        if (!method_exists($this, 'action' . $this->action)) {
            return $this->setResponse('error', 'Action func not found. #' . 'action' . $this->action);
        }
        if (EticTools::postVal('iapi_xfvv') != EticConfig::get('GARANTIBBVA_XFVV')) {
            return $this->setResponse('error', 'XFVV not matched');
        }
        $f_name = 'action' . $this->action;
        return $this->$f_name();
    }

    public function setResponse(string $status = 'success', string $message = '', array $data = [], array $details = [], array $meta = []): self
    {
        $this->response = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'details' => $details,
            'meta' => $meta
        ];

        if ($status != 'success') {
            unset($this->response['data']);
        }

        return $this;
    }

    private function actionSaveApiKeys(): self
    {
        try {
            $publicKey = $this->params['iapi_publicKey'];
            if ($publicKey) {
                EticConfig::set('GARANTIBBVA_PUBLIC_KEY', $publicKey);
            }
            $secretKey = $this->params['iapi_secretKey'];
            if ($secretKey) {
                EticConfig::set('GARANTIBBVA_SECRET_KEY', $secretKey);
            }
            $this->setResponse('success', 'Api keys saved');
            return $this;
        } catch (\Exception $e) {
            $this->setResponse('error', $e->getMessage());
            return $this;
        }
    }

    private function actionCheckApiKeys(): self
    {
        try {
            if (!EticConfig::get('GARANTIBBVA_PUBLIC_KEY') || !EticConfig::get('GARANTIBBVA_SECRET_KEY')) {
                $this->setResponse('error', 'Api keys not found');
                return $this;
            }
            $apiClient = ApiClient::getInstanse();
            $this->response = $apiClient->post('/check/accesstoken', [
                'accesstoken' => $this->params['iapi_accessToken']
            ]);

            return $this;
        } catch (\Exception $e) {
            $this->setResponse('error', $e->getMessage());
            return $this;
        }
    }

    private function actionSetInstallmentOptions(): self
    {
        try {
            $installmentOptions = $this->params['iapi_installmentOptions'];
            if (empty($installmentOptions)) {
                $this->setResponse('error', 'Invalid installment options');
                return $this;
            }
            EticConfig::set('GARANTIBBVA_INSTALLMENTS', json_encode($installmentOptions));
            $this->setResponse('success', 'Installment options updated');
            return $this;
        } catch (\Exception $e) {
            $this->setResponse('error', $e->getMessage());
            return $this;
        }
    }

    private function actionCreatePaymentLink(): self
    {
        try {
            // contexts
            $currency_obj = EticContext::get('currency');
            $currency = $currency_obj->iso_code;
            $cart = EticContext::get('cart');
            $customer = EticContext::get('customer');
            $cart_items = $cart->getProducts();
            $cart_total = $cart->getOrderTotal();
            // Get order id
            $order_id = $cart->id;

            // Get shipping costs
            $shipping_cost = $cart->getTotalShippingCost();
            // Add shipping as cart item if cost exists
            // Get cart discounts/cart rules
            $cartRules = $cart->getCartRules();
            $discounts = [];

            if (!empty($cartRules)) {
                foreach ($cartRules as $rule) {
                    $discounts[] = [
                        'id' => $rule['id_cart_rule'],
                        'name' => $rule['name'],
                        'value' => $rule['value_real'],
                        'value_tax_exc' => $rule['value_tax_exc']
                    ];
                }
            }

            // addresses 
            $shippingAddress = new \Address($cart->id_address_delivery);
            //$invoiceAddress = new \Address($cart->id_address_invoice);
            $adress = new \Address($cart->id_address_delivery);

            // Create Cart instance
            $cartModel = new Cart();

            // Add products to cart
            // discount vs de kontrol edilip eklenilecek
            foreach ($cart_items as $product) {
                $cartItem = new CartItem(
                    'PRD-' . $product['id_product'],
                    $product['name'],
                    'product',
                    number_format($product['price_wt'], 2, '.', ''),
                    $product['quantity']
                );
                $cartModel->addItem($cartItem);
            }

            // Add discounts to cart
            foreach ($discounts as $discount) {
                $discountItem = new CartItem(
                    'DSC-' . $discount['id'],
                    $discount['name'],
                    'discount',
                    number_format($discount['value'], 2, '.', ''),
                    1
                );
                $cartModel->addItem($discountItem);
            }

            if ($shipping_cost > 0) {
                $shippingItem = new CartItem(
                    'SHP-1',
                    'Kargo Ücreti',
                    'shipping',
                    number_format($shipping_cost, 2, '.', ''),
                    1
                );
                $cartModel->addItem($shippingItem);
            }


            $payment = new PaymentModel();
            $payment->setAmount($cart_total);
            $payment->setCurrency($currency);
            $payment->setBuyerFee(0);
            $payment->setMethod('creditcard');
            $payment->setMerchantReference($order_id);

            $payerAddress = new Address();
            $payerAddress->setLine1($adress->address1);
            $payerAddress->setCity($adress->city);
            $payerAddress->setState($adress->country);
            $payerAddress->setPostalCode($adress->postcode);
            $payerAddress->setCountry($adress->country);


            $shippingPhone = $shippingAddress->phone ?: ($shippingAddress->phone_mobile ?: '5000000000');
            $phone = !empty($customer->phone) ? $customer->phone : $shippingPhone;


            $payer = new Payer();
            $payer->setFirstName($customer->firstname);
            $payer->setLastName($customer->lastname);
            $payer->setEmail($customer->email);
            $payer->setPhone($phone);
            $payer->setAddress($payerAddress);
            $payer->setIp($_SERVER['REMOTE_ADDR']);


            $invoice = new Invoice();
            $invoice->setId($order_id);
            $invoice->setFirstName($customer->firstname);
            $invoice->setLastName($customer->lastname);
            $invoice->setPrice($cart_total);
            $invoice->setQuantity(1);


            $shipping = new Shipping();
            $shipping->setFirstName($customer->firstname);
            $shipping->setLastName($customer->lastname);
            $shipping->setPhone($shippingPhone);
            $shipping->setEmail($customer->email);
            $shipping->setAddress($payerAddress);

            $order = new Order();
            $order->setCart($cartModel->toArray()['items']);
            $order->setShipping($shipping);
            $order->setInvoice($invoice);


            $paymentRequest = new PaymentRequest();
            $paymentRequest->setPayment($payment);
            $paymentRequest->setPayer($payer);
            $paymentRequest->setOrder($order);


            $result = Payment::createPayment($paymentRequest->toArray());

            $this->response = $result;
            return $this;
        } catch (\Exception $e) {
            $this->setResponse('error', $e->getMessage());
            return $this;
        }
    }

    private function actionConfirmOrder(): self
    {
        $cart = EticContext::get('cart');
        $customer = new \Customer($cart->id_customer);
        $link = EticContext::get('link');

        try {
            $process_token = $this->params['process_token'];
            $res = Payment::validatePayment($process_token);

            if ($res['status'] != 'success') {
                $redirect_url = $link->getPageLink('index.php?controller=order&step=1', true, null, []);
                $this->setResponse('error', 'Order confirmation failed', [], [
                    'redirect_url' => $redirect_url
                ]);
                return $this;
            }

            $processData = $res['data']['process'];
            $data = $res['data']['transaction'];

            if ($data['status'] == 'completed' && $processData['process_status'] == 'completed') {
                $transaction_amount = $processData['amount'];

                $this->module->validateOrder(
                    $cart->id,
                    empty(EticConfig::get('GARANTIBBVA_ORDER_STATUS')) ? EticConfig::get('PS_OS_PAYMENT') : EticConfig::get('GARANTIBBVA_ORDER_STATUS'),
                    $cart->getOrderTotal(true, \Cart::BOTH),
                    $this->module->displayName,
                    null,
                    [],
                    null,
                    false,
                    $customer->secure_key
                );

                $order = new \Order($this->module->currentOrder);
                $order->total_paid_tax_incl = $transaction_amount;
                $order->total_paid = $transaction_amount;
                $order->update();

                $redirect_url = $link->getPageLink('order-confirmation', true, null, [
                    'id_cart' => $cart->id,
                    'id_module' => $this->module->id,
                    'id_order' => $this->module->currentOrder,
                    'key' => $order->secure_key
                ]);
                $this->setResponse('success', 'Order confirmed', [
                    'redirect_url' => $redirect_url
                ]);
                return $this;
            } else {
                $this->module->validateOrder(
                    $cart->id,
                    EticConfig::get('PS_OS_ERROR'),
                    $cart->getOrderTotal(true, \Cart::BOTH),
                    $this->module->displayName,
                    null,
                    [],
                    null,
                    false,
                    $customer->secure_key
                );
            }
        } catch (\Exception $e) {
            $redirect_url = $link->getPageLink('index.php?controller=order&step=1', true, null, []);
            $this->setResponse('error', 'Order confirmation failed', [
                'redirect_url' => $redirect_url
            ]);
        }

        $this->setResponse('success', 'Order confirmation');
        return $this;
    }

    private function actionSetModuleSettings(): self
    {
        $settings = $this->params['iapi_moduleSettings'];
        try {
            if ($settings) {
                foreach ($settings as $key => $value) {
                    EticConfig::set('GARANTIBBVA_' . strtoupper($key), $value);
                }
                $this->setResponse('success', 'Module settings updated');
            } else {
                $this->setResponse('error', 'Invalid module settings');
            }
        } catch (\Exception $e) {
            $this->setResponse('error', 'Failed to update module settings: ' . $e->getMessage());
        }
        return $this;
    }

    private function actionGetMerchantInfo(): self
    {
       
        $shop = EticContext::get('shop');
        $id_country = $shop->getAddress()->id_country;
        $address = [
            'country' => \Country::getIsoById($id_country)
        ];
        
        $data = [
            'store' => [
                'name' => EticConfig::get(strtoupper(_DB_PREFIX_ . 'SHOP_NAME')),
                'url' => EticContext::get('link')->getBaseLink(),
                'admin_email' => EticConfig::get('PS_SHOP_EMAIL'),
                'phone' => EticConfig::get('PS_SHOP_PHONE'),
                'address' => $address,
                'language' => str_replace('-', '_', EticContext::get('language')->locale),
            ],
            'payment' => [
                'currency' => EticContext::get('currency')->iso_code,
                'currency_symbol' => EticContext::get('currency')->sign,
            ]
        ];
        $this->setResponse('success', 'Merchant info', $data);
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
