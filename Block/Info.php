<?php

namespace Watts25\Naranja\Block;

/**
 * Class Info
 *
 * @package Watts25\Naranja\Block
 */
class Info extends \Magento\Payment\Block\Info
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Prepare information specific to current payment method
     *
     * @param null | array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];

        $info = $this->getInfo();
        $paymentResponse = $info->getAdditionalInformation("paymentResponse");

        if (isset($paymentResponse['id'])) {
            $title = __('Payment id');
            $data[$title->__toString()] = $paymentResponse['id'];
        }

        if (isset($paymentResponse['card'])) {
            $title = __('Card Number');
            $data[$title->__toString()] = $paymentResponse['card'];
        }

        if (isset($paymentResponse['status'])) {
            $title = __('Payment Status');
            $data[$title->__toString()] = ucfirst($paymentResponse['status']);
        }

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
