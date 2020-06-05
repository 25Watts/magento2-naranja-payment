<?php

namespace Watts25\Naranja\Controller\Notifications;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Watts25\Naranja\Helper\Data;
use Watts25\Naranja\Model\WebCheckout\Payment;

class Webcheckout extends NotificationBase
{
    const LOG_NAME = 'basic_notification';

    protected $_paymentFactory;
    protected $coreHelper;
    protected $coreModel;
    protected $_finalStatus = ['rejected', 'cancelled', 'refunded', 'charged_back'];
    protected $_notFinalStatus = ['authorized', 'process', 'in_mediation'];
    protected $_orderFactory;
    protected $_notifications;

    /**
     * Basic constructor.
     * @param Context $context
     * @param Payment $paymentFactory
     * @param Data $coreHelper
     * @param Core $coreModel
     * @param OrderFactory $orderFactory
     * @param Notifications $notifications
     */
    public function __construct(
        Context $context,
        Payment $paymentFactory,
        Data $coreHelper,
        OrderFactory $orderFactory
    ) {
        $this->_paymentFactory = $paymentFactory;
        $this->coreHelper = $coreHelper;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $request = $this->getRequest();
        try {
          // TO-DO: procesar notificación
        } catch (\Exception $e) {
            $this->setResponseHttp($e->getCode(), $e->getMessage(), $request->getParams());
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

        $this->coreHelper->log("NotificationsBasic::setResponseHttp - Response: " . json_encode($response), self::LOG_NAME);

        $this->getResponse()->setHeader('Content-Type', 'application/json', $overwriteExisting = true);
        $this->getResponse()->setBody(json_encode($response));
        $this->getResponse()->setHttpResponseCode($httpStatus);

        return;
    }



}
