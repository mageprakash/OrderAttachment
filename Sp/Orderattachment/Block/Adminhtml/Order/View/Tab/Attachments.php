<?php
namespace Sp\Orderattachment\Block\Adminhtml\Order\View\Tab;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Attachments extends AbstractOrder implements TabInterface
{
    /**
     * @var \Sp\Orderattachment\Helper\Attachment
     */
    protected $attachmentHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Sp\Orderattachment\Helper\Attachment $attachmentHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Sp\Orderattachment\Helper\Attachment $attachmentHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->attachmentHelper = $attachmentHelper;
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getAttachmentConfig()
    {
        $config = $this->attachmentHelper->getAttachmentConfig($this);

        return $config;
    }

    public function getOrderAttachments()
    {
        $orderId = $this->getOrder()->getId();

        return $this->attachmentHelper->getOrderAttachments($orderId);
    }

    public function getUploadUrl()
    {
        return $this->getUrl(
            'orderattachment/attachment/upload',
            ['order_id' => $this->getOrder()->getId()]
        );
    }

    public function getUpdateUrl()
    {
        return $this->getUrl(
            'orderattachment/attachment/update',
            ['order_id' => $this->getOrder()->getId()]
        );
    }

    public function getRemoveUrl()
    {
        return $this->getUrl(
            'orderattachment/attachment/delete',
            ['order_id' => $this->getOrder()->getId()]
        );
    }

    public function getTabLabel()
    {
        return __('Order Attachments');
    }

    public function getTabTitle()
    {
        return __('Order Attachments');
    }

    public function canShowTab()
    {
        return $this->attachmentHelper->isOrderAttachmentEnabled();
    }

    public function isHidden()
    {
        return false;
    }
}
