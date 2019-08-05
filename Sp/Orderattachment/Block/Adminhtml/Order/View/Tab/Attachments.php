<?php
namespace Sp\Orderattachment\Block\Adminhtml\Order\View\Tab;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Store\Model\ScopeInterface;
use Sp\Orderattachment\Model\Attachment;

class Attachments extends AbstractOrder implements TabInterface
{
    /**
     * @var \Sp\Orderattachment\Helper\Attachment
     */
    protected $attachmentHelper;

    /**
     * @var \Sp\Orderattachment\Helper\Attachment
     */
    protected $dataHelper;
   
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

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
        \Sp\Orderattachment\Helper\Data $dataHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->attachmentHelper = $attachmentHelper;
        $this->dataHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getAttachmentConfig()
    {
        $config = $this->dataHelper->getAttachmentConfig($this);

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
        return __($this->scopeConfig->getValue(Attachment::XML_PATH_ATTACHMENT_ON_ATTACHMENT_TITLE, ScopeInterface::SCOPE_STORE));
    }

    public function getTabTitle()
    {
        return __($this->scopeConfig->getValue(Attachment::XML_PATH_ATTACHMENT_ON_ATTACHMENT_TITLE, ScopeInterface::SCOPE_STORE));
    }

    public function canShowTab()
    {
        return $this->dataHelper->isOrderAttachmentEnabled();
    }

    public function isHidden()
    {
        return false;
    }
}
