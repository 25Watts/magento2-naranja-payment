<?php

namespace Watts25\Naranja\Controller\Webcheckout;

use Exception;
use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlInterface;
use Watts25\Naranja\Model\WebCheckout\Payment;
use Watts25\Naranja\Helper\Data;

class Pay extends Action implements ViewInterface
{

    const LOG_NAME = 'CONTROLLER_WEBCHECKOUT_PAY';
    /**
     * @var \Watts25\Naranja\Model\WebCheckout
     */
    protected $_paymentFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var
     */
    protected $_messageManager;

    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * @var
     */
    protected $_url;

    /**
     * @var
     */
    protected $_helper;

    /**
     * Pay constructor.
     * @param Context $context
     * @param Payment $paymentFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param UrlInterface $urlInterface
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Payment $paymentFactory,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        UrlInterface $urlInterface,
        Data $helper
    ) {
        $this->_paymentFactory = $paymentFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_messageManager = $messageManager;
        $this->_resultFactory = $resultFactory;
        $this->_url = $urlInterface;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $paymentRequest = $this->_paymentFactory->createPaymentRequest();
            $resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
    
            $resultRedirect->setUrl($paymentRequest['checkout_url']);

            return $resultRedirect;
        } catch (Exception $e) {
            $this->_helper->log("ERROR CONTROLLER WEBCHECKOUT PAY: " . $e->getMessage(), self::LOG_NAME);
        }
    }
}
