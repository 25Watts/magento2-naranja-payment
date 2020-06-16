<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Watts25\Naranja\Model\WebCheckout;

use Exception;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as customerSession;

/**
 * Pay In Store payment method model
 */
class Payment extends AbstractMethod
{
    const CODE = 'naranja_webcheckout';
    const ACTION_URL = 'naranja/webcheckout/pay';
    const CHECKOUT_SUCCESS_URL = 'checkout/onepage/success';
    const CHECKOUT_FAILURE_URL = 'checkout/onepage/failure';

    protected $_urlBuilder;
    protected $_naranjaCheckout;
    protected $_orderFactory;
    protected $_checkoutSession;
    protected $_customerSession;
    protected $_helper;

    /**
     *  Overrides fields
     */
    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment = true;
    protected $_infoBlockType = 'Watts25\Naranja\Block\Info';
    protected $_isInitializeNeeded = true;

    public function __construct(
        OrderFactory $orderFactory,
        Session $checkoutSession,
        customerSession $customerSession,
        UrlInterface $urlBuilder,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        \Watts25\Naranja\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );

        $this->_orderFactory = $orderFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_helper = $helper;
        $this->_naranjaCheckout = $this->_helper->getApiInstance();
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_urlBuilder->getUrl(self::ACTION_URL);
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        $successUrl = 'naranja/checkout/page';
        return $this->_urlBuilder->getUrl($successUrl, ['_secure' => true]);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createPaymentRequest()
    {
        try{
            $orderId = $this->_checkoutSession->getLastRealOrderId();
            $order = $this->_orderFactory->create()->loadByIncrementId($orderId);
            $customer = $this->_customerSession->getCustomer();
            // $infoInstance = $this->getInfoInstance();
            $productItems = [];

            // Definimos el objeto transaccion
            $transaction = new \Naranja\CheckoutApi\Model\Transaction();

            // Definimos el Amount
            $amountTransaction = new \Naranja\CheckoutApi\Model\Amount();
            $amountTransaction->setCurrency('ARS'); 
            $amountTransaction->setValue((string)number_format($order->getBaseGrandTotal(), 2, '.', ''));

            // Agregamos el objeto amount a la transaccion
            $transaction->setAmount($amountTransaction);
            $transaction->setSoftDescriptor('GOFRIZ CONGELADOS');

            foreach ($order->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                //$image = $this->_helperImage->init($product, 'image');
                //$image->getUrl();

                // Set product to checkout api
                $productItem = new \Naranja\CheckoutApi\Model\ProductItem();
                $productItem->setName($product->getName());
                //$productItem->setDescription('Granja del sol');
                $productItem->setQuantity((int)number_format($item->getQtyOrdered(), 0, '.', ''));

                // Set unit_price
                $unitPrice = new \Naranja\CheckoutApi\Model\Amount();
                $unitPrice->setCurrency('ARS');
                $unitPrice->setValue((string)number_format($item->getPrice(), 2, '.', ''));

                // Add el unitPrice to product
                $productItem->setUnitPrice($unitPrice);

                $productItems[] = $productItem;
            }

            // add shipping item
            $shippingItem = new \Naranja\CheckoutApi\Model\ProductItem();
            $shippingItem->setName($order->getShippingDescription());
            //$shippingItem->setDescription('Granja del sol');
            $shippingItem->setQuantity(1);

            // Set unit_price
            $unitPrice = new \Naranja\CheckoutApi\Model\Amount();
            $unitPrice->setCurrency('ARS');
            $unitPrice->setValue((string)number_format($order->getBaseShippingAmount(), 2, '.', ''));

            // Add el unitPrice to product
            $shippingItem->setUnitPrice($unitPrice);

            $productItems[] = $shippingItem;

            // add product to transaction
            $transaction->setProducts($productItems);

            // Generamos el payment request
            $paymentRequest = new \Naranja\CheckoutApi\Model\PaymentRequest();
            $paymentRequest->setPaymentType('web_checkout');
            $paymentRequest->setAuthorizationMode('SALE');
            $paymentRequest->setExternalPaymentId($order->getIncrementId());
            $paymentRequest->setTransactions([$transaction]);

            // Definimos el Requests creation redirect
            $requestsCreationRedirect = new \Naranja\CheckoutApi\Model\RequestCreationRedirect();
            $requestsCreationRedirect->setSuccessUrl($this->_urlBuilder->getUrl(self::CHECKOUT_SUCCESS_URL));
            $requestsCreationRedirect->setFailureUrl($this->_urlBuilder->getUrl(self::CHECKOUT_FAILURE_URL));

            // Agregamos el requests redirect al paymenRequests
            $paymentRequest->setRequestCreationRedirect($requestsCreationRedirect);
            // $paymentRequest->setCallbackUrl($this->_urlBuilder->getUrl('naranja/webcheckout/notification'));
            $paymentRequest->setCallbackUrl('https://webhook.site/eebf626f-f34a-4aa2-813f-54fe95890904');

            // Ejecutamos el metodo
            $response = $this->_naranjaCheckout->createPaymentRequest($paymentRequest);
            $response = json_decode($response,true);

            // add additionalInformation
            // $infoInstance->setAdditionalInformation($response);

            return $response;
        } catch (Exception $e){
            return [];
        }

    }
}
