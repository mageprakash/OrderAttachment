<?php


namespace Sp\Orderattachment\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class AttachmentDisplayOptions implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('After Shipping Address'),
                'value' => 'after-shipping-address'
            ],
            [
                'label' => __('After Shipping Methods'),
                'value' => 'after-shipping-methods'
            ],
            [
                'label' => __('After Payment Method'),
                'value' => 'after-payment-methods'
            ]
        ];
    }
}
