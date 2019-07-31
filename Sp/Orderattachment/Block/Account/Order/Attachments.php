<?php
namespace Sp\Orderattachment\Block\Account\Order;

class Attachments extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'account/order/attachments.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Sp\Orderattachment\Helper\Attachment
     */
    protected $attachmentHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Sp\Orderattachment\Helper\Attachment $attachmentHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Sp\Orderattachment\Helper\Attachment $attachmentHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->attachmentHelper = $attachmentHelper;
    }

    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    public function isOrderAttachmentEnabled()
    {
        return $this->attachmentHelper->isOrderAttachmentEnabled();
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

    public function isAllowedFileUpload()
    {
        return $this->attachmentHelper->isAllowedFileUpload();
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
}
