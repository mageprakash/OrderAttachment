<?php
namespace Sp\Orderattachment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Sp\Orderattachment\Model\Attachment;

class AttachmentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Sp\Orderattachment\Model\ResourceModel\Attachment\Collection
     */
    protected $attachmentCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Sp\Orderattachment\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     * @param CheckoutSession $checkoutSession
     * @param \Sp\Orderattachment\Model\ResourceModel\Attachment\Collection $attachmentCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        CheckoutSession $checkoutSession,
        \Sp\Orderattachment\Model\ResourceModel\Attachment\Collection $attachmentCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Sp\Orderattachment\Helper\Data $dataHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->attachmentCollection = $attachmentCollection;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
    }

    public function getConfig()
    {
        $attachSize = $this->getOrderAttachmentFileSize();
        return [
            'spAttachmentEnabled' => $this->isOrderAttachmentEnabled(),
            'attachments' => $this->getUploadedAttachments(),
            'spAttachmentLimit'    => $this->getOrderAttachmentFileLimit(),
            'spAttachmentSize'     => $this->getOrderAttachmentFileSize(),
            'spAttachmentExt'      => $this->getOrderAttachmentFileExt(),
            'spAttachmentUpload'   => $this->getAttachmentUploadUrl(),
            'spAttachmentUpdate'   => $this->getAttachmentUpdateUrl(),
            'spAttachmentRemove'   => $this->getAttachmentRemoveUrl(),
            'removeItem' => __('Remove Item'),
            'spAttachmentInvalidExt' => __('Invalid File Type'),
            'spAttachmentComment' => __('Write comment here'),
            'spAttachmentInvalidSize' => __('Size of the file is greather than allowed') . '(' . $attachSize . ' KB)',
            'spAttachmentInvalidLimit' => __('You have reached the limit of files'),
            'spAttachmentTitle' =>  $this->dataHelper->getTitle(),
            'spAttachmentInfromation' => $this->scopeConfig->getValue( Attachment::XML_PATH_ATTACHMENT_ON_ATTACHMENT_INFORMATION, ScopeInterface::SCOPE_STORE )
        ];
    }

    private function getUploadedAttachments()
    {
        if ($quoteId = $this->checkoutSession->getQuote()->getId()) {
            $attachments = $this->attachmentCollection
                ->addFieldToFilter('quote_id', $quoteId)
                ->addFieldToFilter('order_id', ['is' => new \Zend_Db_Expr('null')]);

            $defaultStoreId = $this->storeManager->getDefaultStoreView()->getStoreId();
            foreach ($attachments as $attachment) {
                $url = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "orderattachment/" . $attachment['path'];
                $attachment->setUrl($url);
                $preview = $this->storeManager->getStore($defaultStoreId)->getUrl(
                    'orderattachment/attachment/preview',
                    [
                        'attachment' => $attachment['attachment_id'],
                        'hash' => $attachment['hash']
                    ]
                );
                $attachment->setPreview($preview);
                $attachment->setPath(basename($attachment->getPath()));
            }
            $result = $attachments->toArray();
            /*echo "<pre>";
            print_r( $result);
            die;*/

            foreach ($result['items'] as $key => $value) {
                $result['items'][$key]['attachment_class'] = 'sp-attachment-id'.$value['attachment_id'];
                $result['items'][$key]['hash_class'] = 'sp-attachment-hash'.$value['attachment_id'] ;
            }
            
            $result = $result['items'];
            return $result;
        }

        return false;
    }

    private function isOrderAttachmentEnabled()
    {
        $moduleEnabled = $this->scopeConfig->getValue(
            Attachment::XML_PATH_ENABLE_ATTACHMENT,
            ScopeInterface::SCOPE_STORE
        );

        return ($moduleEnabled);
    }

    private function getOrderAttachmentFileLimit()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getOrderAttachmentFileSize()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_SIZE,
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getOrderAttachmentFileExt()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_EXT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAttachmentUploadUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/upload');
    }

    public function getAttachmentUpdateUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/update');
    }

    public function getAttachmentRemoveUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/delete');
    }
}
