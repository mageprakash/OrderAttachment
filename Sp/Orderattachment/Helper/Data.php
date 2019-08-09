<?php
namespace Sp\Orderattachment\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Get title
     * @return boolean
     */
    public function getTitle()
    {
        $titleValue = $this->scopeConfig->getValue(
            \Sp\Orderattachment\Model\Attachment::XML_PATH_ATTACHMENT_ON_ATTACHMENT_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

     
        return (trim($titleValue))?$titleValue:\Sp\Orderattachment\Model\Attachment::DEFAULT_TITLE_ATTACHMENT;
    }
    
    /**
     * Get config for order attachments enabled
     * @return boolean
     */
    public function isOrderAttachmentEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            \Sp\Orderattachment\Model\Attachment::XML_PATH_ENABLE_ATTACHMENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get attachment config json
     * @param mixed $block
     * @return string
     */
    public function getAttachmentConfig($block)
    {
        $config = [
            'attachments' => $block->getOrderAttachments(),
            'limit' => $this->scopeConfig->getValue(
                \Sp\Orderattachment\Model\Attachment::XML_PATH_ATTACHMENT_FILE_LIMIT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'size' => $this->scopeConfig->getValue(
                \Sp\Orderattachment\Model\Attachment::XML_PATH_ATTACHMENT_FILE_SIZE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'ext' => $this->scopeConfig->getValue(
                \Sp\Orderattachment\Model\Attachment::XML_PATH_ATTACHMENT_FILE_EXT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'uploadUrl' => $block->getUploadUrl(),
            'updateUrl' => $block->getUpdateUrl(),
            'removeUrl' => $block->getRemoveUrl()
        ];

        return $this->jsonEncoder->encode($config);
    }
}
