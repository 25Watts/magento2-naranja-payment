<?php

namespace Watts25\Naranja\Controller\Notifications;

use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Watts25\Naranja\Helper\Data;
use Watts25\Naranja\Model\WebCheckout\Payment;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order;

class Webcheckout extends NotificationBase
{
    const LOG_NAME = 'webcheckout_notification';

    protected $_paymentFactory;
    protected $_helper;
    protected $_orderFactory;
    protected $_notifications;
    protected $_naranjaCheckout;
    protected $_transactionFactory;
    protected $_invoiceSender;

    public function __construct(
        Context $context,
        Payment $paymentFactory,
        Data $helper,
        OrderFactory $orderFactory,
        TransactionFactory $transactionFactory,
        InvoiceSender $invoiceSender
    ) {
        $this->_paymentFactory = $paymentFactory;
        $this->_helper = $helper;
        $this->_orderFactory = $orderFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_invoiceSender = $invoiceSender;
        parent::__construct($context);

        $this->_naranjaCheckout = $this->_helper->getApiInstance();
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $request = $this->getRequest();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['payment_id']) || empty($data['external_payment_id'])) {
                throw new Exception(__('Error Payment notification is expected'), 400);
            }

            $paymentId = $data['payment_id'];
            $order = $this->_orderFactory->create()->loadByIncrementId($data['external_payment_id']);

            if (empty($order) || empty($order->getId())) {
                throw new Exception(__('Error Order Not Found in Magento: ') . $data['external_reference'], 400);
            }

            if ($order->getState() == Order::STATE_CANCELED) {
                throw new Exception(__('Order already canceled: ') . $data["external_reference"], 400);
            }

            $payment = $this->_naranjaCheckout->getPayment($paymentId);

            if (empty($payment)) {
                throw new Exception(__('Error Payment not found in Naranja'), 400);
            }

            $this->_updatePaymentInfo($order, $payment);

            $this->_helper->log(
                sprintf(
                    "Payment id: %s \\n %s",
                    $paymentId,
                    $payment->__toString()
                )
            );

            switch ($data['status']) {
                case 'APPROVED':
                    $message = __('Transaction automatically approved by Naranja');

                    $order
                        ->setState(Order::STATE_PROCESSING)
                        ->setStatus(Order::STATE_PROCESSING)
                        ->addStatusToHistory(Order::STATE_PROCESSING, $message)->save();

                    $this->_createInvoice($order, $message);
                    break;

                case 'REJECTED':
                    $message = __('Transaction automatically denied by Naranja');

                    $order
                        ->setState(Order::STATE_PENDING_PAYMENT)
                        ->setStatus(Order::STATE_PENDING_PAYMENT)
                        ->addStatusToHistory(Order::STATE_PENDING_PAYMENT, $message)->save();
                    break;
            }
        } catch (Exception $e) {
            $this->setResponseHttp(500, $e->getMessage(), $request->getParams());
        }
    }

    /**
     * @param $httpStatus
     * @param $message
     * @param array $data
     */
    protected function setResponseHttp($httpStatus, $message, $data = [])
    {
        $response = [
            "status" => $httpStatus,
            "message" => $message,
            "data" => $data
        ];

        $this->_helper->log("NotificationsBasic::setResponseHttp - Response: " . json_encode($response), self::LOG_NAME);

        $this->getResponse()->setHeader('Content-Type', 'application/json', $overwriteExisting = true);
        $this->getResponse()->setBody(json_encode($response));
        $this->getResponse()->setHttpResponseCode($httpStatus);

        return;
    }

    /**
     * @param $order
     * @param $data
     */
    private function _updatePaymentInfo($order, $data)
    {
        $paymentOrder = $order->getPayment();

        $additionalFields = [
            'id',
            'payment_type',
            'status',
            'external_payment_id',
            'date_created'
        ];

        foreach ($additionalFields as $field) {
            if (isset($data[$field])) {
                $paymentOrder->setAdditionalInformation($field, $data[$field]);
            }
        }

        if (isset($data['transactions'][0]['installments_plan']['installments'])) {
            $paymentOrder->setAdditionalInformation('installments', $data['transactions'][0]['installments_plan']['installments']);
        }

        $paymentOrder->save();
    }

    private function _createInvoice($order, $message)
    {
        if (!$order->hasInvoices()) {
            $invoice = $order->prepareInvoice();
            $invoice->register();
            $invoice->pay();
            $invoice->addComment($message);

            $transaction = $this->_transactionFactory->create();
            $transaction->addObject($invoice);
            $transaction->addObject($invoice->getOrder());
            $transaction->save();

            $this->_invoiceSender->send($invoice, true, $message);

            return true;
        }

        return false;
    }
}
