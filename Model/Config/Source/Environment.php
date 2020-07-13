<?php
namespace Watts25\Naranja\Model\Config\Source;

/**
 * Order Status source model
 */
class Environment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => 'sandbox', 'label' => __('Sandbox')],
            ['value' => 'staging', 'label' => __('Staging')],
            ['value' => 'production', 'label' => __('Production')]
        ];
        return $options;
    }
}
