<?php

namespace Watts25\Naranja\Model\WebCheckout;

use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Action\Context;
use Watts25\Naranja\Model\WebCheckout;
use Watts25\Naranja\Helper\Data;

class ConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = WebCheckout\Payment::CODE;
    protected $_scopeConfig;
    protected $_methodInstance;
    protected $_helper;
    protected $_context;
    protected $_assetRepo;

    /**
     * BasicConfigProvider constructor.
     * @param Context $context
     * @param PaymentHelper $paymentHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param Repository $assetRepo
     * @param ProductMetadataInterface $productMetadata
     * @param Data $coreHelper
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        Context $context,
        PaymentHelper $paymentHelper,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        Repository $assetRepo
    ) {
        $this->_methodInstance = $paymentHelper->getMethodInstance($this->methodCode);
        $this->_scopeConfig = $scopeConfig;
        $this->_context = $context;
        $this->_helper = $helper;
        $this->_assetRepo = $assetRepo;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        try {
            if (!$this->_methodInstance->isAvailable()) {
                return [];
            }

            $data = [
                'payment' => [
                    $this->methodCode => [
                        'actionUrl' => $this->_context->getUrl()->getUrl(WebCheckout\Payment::ACTION_URL),
                        'logoUrl'   => $this->_assetRepo->getUrl("Watts25_Naranja::images/logo.svg"),
                    ],
                ],
            ];

            return $data;
        } catch (\Exception $e) {
            $this->_helper->log("BasicConfigProvider ERROR: " . $e->getMessage(), 'BasicConfigProvider');
            return [];
        }
    }
}
