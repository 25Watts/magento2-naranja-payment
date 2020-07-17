<?php

namespace Watts25\Naranja\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class EnabledPaymentByCustomerGroup implements ObserverInterface
{
    protected $_logger;
    protected $_helper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Watts25\Naranja\Helper\Data $helper
    )
    {
        $this->_logger = $logger;
        $this->_helper = $helper;
    }

    /**
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $result          = $observer->getEvent()->getResult();
        $methodInstance  = $observer->getEvent()->getMethodInstance();
        $quote           = $observer->getEvent()->getQuote();
        $customerGroup   = $this->_helper->getEnabledCustomerGroup();

        //$this->_logger->info('enabled customer gruoup ' . $customerGroup);

        // If Cusomer group is match then work
        if (null !== $quote && $quote->getCustomerGroupId() != $customerGroup) {
            // Disable All payment gateway exclude Your payment Gateway
            if ($methodInstance->getCode() == 'naranja_webcheckout') {
                $result->setData('is_available', false);
            }
        }
    }
}
