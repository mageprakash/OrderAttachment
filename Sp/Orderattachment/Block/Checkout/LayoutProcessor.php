<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Sp\Orderattachment\Block\Checkout;

use \Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Sp\Orderattachment\Model\Attachment;


class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{

    const SHIPPING_STEP = 2;

    const BILLING_STEP = 3;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * LayoutProcessor constructor.
     *
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper               $attributeMapper
     * @param AttributeMerger               $merger
     * @param CustomerSession               $customerSession
     * @param Config                        $configHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession

    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     * @throws Exception
     */
    public function process($jsLayout)
    {

        switch ($this->scopeConfig->getValue(Attachment::XML_PATH_ATTACHMENT_ON_DISPLAY_ATTACHMENT, ScopeInterface::SCOPE_STORE)) {
            case 'after-payment-methods':
                $this->addToAfterPaymentMethods($jsLayout);
                break;
            case 'after-shipping-address':
                $this->addToAfterShippingAddress($jsLayout);
                break;
             case 'after-shipping-methods':
                $this->addToAfterShippingMethods($jsLayout);
                break;
            /*
                default:
                # code...
                break;*/
        }

        return $jsLayout;
    }


     protected function addToAfterPaymentMethods(&$jsLayout)
     {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                  ['children']['payment']['children']['afterMethods']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                       ['children']['payment']['children']['afterMethods']['children'];
            
            $fields['order-attachment'] = ['component' => "Sp_Orderattachment/js/view/order/attachment"];
        }

        return $jsLayout;
      }

     protected function addToAfterShippingAddress(&$jsLayout)
     {
         if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                  ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                      ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

            $fields['order-attachment'] =
                [
                    'component' => "Sp_Orderattachment/js/view/order/attachment"
                ];
        }

        return $jsLayout;
      }

     protected function addToAfterShippingMethods(&$jsLayout)
     {
           if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']
          )) {
                $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                          ['children']['shippingAddress']['children'];

                $fields['order-attachment-r'] =
                    [
                        'component' => "uiComponent",
                        'displayArea' => "shippingAdditional",
                        'children' =>  ['attachment'=> ['component' => "Sp_Orderattachment/js/view/order/attachment"]]
                    ];
          }

          return $jsLayout;
      }
}
