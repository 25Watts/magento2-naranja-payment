<?php

namespace Watts25\Naranja\Helper;

use Magento\Framework\View\LayoutFactory;
use Watts25\Naranja\Lib\NaranjaCheckout;

class Data
extends \Magento\Payment\Helper\Data
{
    protected $_logger;
    protected $_apiInstance;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Watts25\Naranja\Logger\Logger $logger
    ) {
        parent::__construct($context, $layoutFactory, $paymentMethodFactory, $appEmulation, $paymentConfig, $initialConfig);
        $this->_logger = $logger;
    }

    public function getApiInstance()
    {
        if (is_null($this->_apiInstance)) {
            $clientId = $this->scopeConfig->getValue(\Watts25\Naranja\Helper\ConfigData::CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $clientSecret = $this->scopeConfig->getValue(\Watts25\Naranja\Helper\ConfigData::CLIENT_SECRET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $environment = $this->scopeConfig->getValue(\Watts25\Naranja\Helper\ConfigData::ENVIRONMENT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
            $this->_apiInstance = new NaranjaCheckout($clientId, $clientSecret, $environment);
        }

        return $this->_apiInstance;
    }

    public function log($message, $name = "watts25_naranja", $array = null)
    {
        //load admin configuration value, default is true
        $actionLog = $this->scopeConfig->getValue(\Watts25\Naranja\Helper\ConfigData::PATH_LOG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$actionLog) {
            return;
        }

        //if extra data is provided, it's encoded for better visualization
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        //set log
        $this->_logger->setName($name);
        $this->_logger->debug($message);
    }

    public function getEnabledCustomerGroup()
    {
        $customerGroup = $this->scopeConfig->getValue(\Watts25\Naranja\Helper\ConfigData::ENABLED_CUSTOMER_GROUP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $customerGroup;
    }
}
