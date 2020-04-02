<?php
namespace Sp\Orderattachment\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Attachment extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Sp\Orderattachment\Helper\Upload
     */
    protected $uploadModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Sp\Orderattachment\Model\ResourceModel\Attachment\Collection
     */
    protected $attachmentCollection;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $random;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Sp\Orderattachment\Model\AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Sp\Orderattachment\Helper\Upload $uploadModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Math\Random $random
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Sp\Orderattachment\Model\AttachmentFactory $attachmentFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Sp\Orderattachment\Helper\Upload $uploadModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Math\Random $random,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Sp\Orderattachment\Model\AttachmentFactory $attachmentFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Sp\Orderattachment\Model\ResourceModel\Attachment\Collection $attachmentCollection
    ) {
        parent::__construct($context);
        $this->uploadModel = $uploadModel;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->random = $random;
        $this->dateTime = $dateTime;
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentCollection = $attachmentCollection;
        $this->fileSystem = $fileSystem;
        $this->escaper = $escaper;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Upload file and save attachment
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    public function saveAttachment($request)
    {
        try {

            $uploadData = $request->getFiles()->get('order-attachment')[0];
            $attachments = $this->attachmentCollection;
            $result = $this->uploadModel->uploadFileAndGetInfo($uploadData);

            unset($result['tmp_name']);
            unset($result['path']);
            $result['success'] = true;
            $result['url'] = $this->storeManager->getStore()
                ->getBaseUrl() . "pub/media/orderattachment/" . $result['file'];

            $hash = $this->random->getRandomString(32);
            $date = $this->dateTime->gmtDate('Y-m-d H:i:s');

            $attachment = $this->attachmentFactory
                ->create()
                ->setPath($result['file'])
                ->setHash($hash)
                ->setComment('')
                ->setType($result['type'])
                ->setUploadedAt($date)
                ->setModifiedAt($date);

            if ($orderId = $request->getParam('order_id')) {
                $attachment->setOrderId($orderId);

                $attachments->addFieldToFilter('quote_id', ['is' => new \Zend_Db_Expr('null')]);
                $attachments->addFieldToFilter('order_id', $orderId);

            } else {
                $quote = $this->checkoutSession->getQuote();
                $attachment->setQuoteId($quote->getId());

                $attachments->addFieldToFilter('quote_id', $quote->getId());
                $attachments->addFieldToFilter('order_id', ['is' => new \Zend_Db_Expr('null')]);


            }

            $attachment->save();

            $defaultStore = $this->storeManager
                ->getStore(
                    $this->storeManager->getDefaultStoreView()->getId()
                );

            $preview = $defaultStore->getUrl(
                'orderattachment/attachment/preview',
                [
                    'attachment' => $attachment->getId(),
                    'hash' => $attachment->getHash()
                ]
            );
            $download = $defaultStore->getUrl(
                'orderattachment/attachment/preview',
                [
                    'attachment' => $attachment->getId(),
                    'hash' => $attachment->getHash(),
                    'download' => 1
                ]
            );

            $url = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "orderattachment/" . $attachment->getPath();

            $result["attachment_count"] = $attachments->getSize();
            $result["quote_id"] = $attachment->getOrderId();
            $result["order_id"] = $attachment->getQuoteId();
            $result['url'] = $url;
            $result['preview'] = $preview;
            $result['path'] = basename($attachment->getPath());
            $result["type"] = $attachment->getType();
            $result["uploaded_at"] = $attachment->getUploadedAt();
            $result["modified_at"] = $attachment->getModifiedAt();
            $result['download'] = $download;
            $result['attachment_id'] = $attachment->getId();
            $result['hash'] = $attachment->getHash();
            $result['hash_class'] = 'sp-attachment-hash'.$attachment->getId();
            $result['attachment_class'] = 'sp-attachment-id'.$attachment->getId();
            $result['comment'] = '';
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * Delete order attachment
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    public function deleteAttachment($request)
    {
        $result = [];
        $isAjax = $request->isAjax();
        $isPost = $request->isPost();
        $requestParams = $request->getParams();
        $attachmentId = $requestParams['attachment'];
        $hash = $requestParams['hash'];
        $orderId = isset($requestParams['order_id']) ? $requestParams['order_id'] : null;
        $attachments = $this->attachmentCollection;

        if (!$isAjax || !$isPost || !$attachmentId || !$hash) {
            return ['success' => false, 'error' => __('Invalid Request Params')];
        }

        try {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);

            if (!$attachment->getId() || ($orderId && $orderId !== $attachment->getOrderId())) {
                return ['success' => false, 'error' => __('Can\'t find a attachment to delete.')];
            }

            if ($hash !== $attachment->getHash()) {
                return ['success' => false, 'error' => __('Invalid Hash Params')];
            }

            $varDirectory = $this->fileSystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath("orderattachment");

            $attachFile = $varDirectory . "/" . $attachment->getPath();
            if (file_exists($attachFile)) {
                unlink($attachFile);
            }
            $attachment->delete();

            if ($attachment->getOrderId()) {
                $attachments->addFieldToFilter('quote_id', ['is' => new \Zend_Db_Expr('null')]);
                $attachments->addFieldToFilter('order_id', $attachment->getOrderId());
            } else {
                $attachments->addFieldToFilter('quote_id', $attachment->getQuoteId());
                $attachments->addFieldToFilter('order_id', ['is' => new \Zend_Db_Expr('null')]);
            }

            $result = ['success' => true,"attachment_count" => $attachments->getSize()];

        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * Save attachment comment
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    public function updateAttachment($request)
    {
        $result = [];
        $isAjax = $request->isAjax();
        $isPost = $request->isPost();
        $requestParams = $request->getParams();
        $attachmentId = $requestParams['attachment'];
        $hash = $requestParams['hash'];
        $comment = $this->escaper->escapeHtml($requestParams['comment']);
        $orderId = isset($requestParams['order_id']) ? $requestParams['order_id'] : null;
        $attachments = $this->attachmentCollection;

        if (!$isAjax || !$isPost || !$attachmentId || !$hash) {
            return ['success' => false, 'error' => __('Invalid Request Params')];
        }

        try {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);

            if (!$attachment->getId() || ($orderId && $orderId !== $attachment->getOrderId())) {
                return ['success' => false, 'error' => __('Can\'t find a attachment to update.')];
            }

            if ($hash !== $attachment->getHash()) {
                return ['success' => false, 'error' => __('Invalid Hash Params')];
            }

            $attachment->setComment($comment);
            $attachment->save();

            if ($attachment->getOrderId()) {
                $attachments->addFieldToFilter('quote_id', ['is' => new \Zend_Db_Expr('null')]);
                $attachments->addFieldToFilter('order_id', $attachment->getOrderId());
            } else {
                $attachments->addFieldToFilter('quote_id', $attachment->getQuoteId());
                $attachments->addFieldToFilter('order_id', ['is' => new \Zend_Db_Expr('null')]);
            }

            $result = ['success' => true,"attachment_count" => $attachments->getSize()];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * Preview attachment
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Controller\Result\Raw $response
     */
    public function previewAttachment($request, $response)
    {
        $result = [];
        $attachmentId = $request->getParam('attachment');
        $hash = $request->getParam('hash');
        $download = $request->getParam('download');

        if (!$attachmentId || !$hash) {
            $result = ['success' => false, 'error' => __('Invalid Request Params')];
            $response->setHeader('Content-type', 'text/plain')
                ->setContents(json_encode($result));

            return $response;
        }

        try {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);

            if (!$attachment->getId()) {
                $result = ['success' => false, 'error' => __('Can\'t find a attachment to preview.')];
                $response->setHeader('Content-type', 'text/plain')
                    ->setContents(json_encode($result));

                return $response;
            }

            if ($hash !== $attachment->getHash()) {
                $result = ['success' => false, 'error' => __('Invalid Hash Params')];
                $response->setHeader('Content-type', 'text/plain')
                    ->setContents(json_encode($result));

                return $response;
            }

            $varDirectory = $this->fileSystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath("orderattachment");
            $attachmentFile = $varDirectory . "/" . $attachment->getPath();

            $attachmentType = explode('/', $attachment->getType());
            $handle = fopen($attachmentFile, "r");
            if ($download) {
                $response
                    ->setHeader('Content-Type', 'application/octet-stream', true)
                    ->setHeader(
                        'Content-Disposition',
                        'attachment; filename="' . basename($attachmentFile) . '"',
                        true
                    );
            } else {
                $response->setHeader('Content-Type', $attachment->getType(), true);
            }
            $response->setContents(fread($handle, filesize($attachmentFile)));
            fclose($handle);
        } catch (\Exception $e) {
            $result = ['success' => false, 'error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $response->setHeader('Content-type', 'text/plain');
            $response->setContents(json_encode($result));
        }

        return $response;
    }

    /**
     * Load order attachments by order id or by quote id
     * @param  int $entityId
     * @param  bool $byOrder load by order or by quote
     * @return array
     */
    public function getOrderAttachments($entityId, $byOrder = true)
    {
        $attachmentModel = $this->attachmentFactory->create();
        if ($byOrder) {
            $attachments = $attachmentModel->getOrderAttachments($entityId);
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "orderattachment/";
        } else {
            $attachments = $attachmentModel->getAttachmentsByQuote($entityId);
        }

        if (count($attachments) > 0) {
            foreach ($attachments as &$attachment) {
                $download = $this->storeManager->getStore()->getUrl(
                    'orderattachment/attachment/preview',
                    [
                        'attachment' => $attachment['attachment_id'],
                        'hash' => $attachment['hash'],
                        'download' => 1
                    ]
                );
                $attachment['path'] = $attachment['path'];
                $attachment['download'] = $download;
                $attachment['comment'] = $this->escaper->escapeHtml($attachment['comment']);

                if ($byOrder) {
                    $preview = $this->_urlBuilder->getUrl(
                        'orderattachment/attachment/preview',
                        [
                            'attachment' => $attachment['attachment_id'],
                            'hash' => $attachment['hash']
                        ]
                    );
                    $attachment['preview'] = $preview;
                    $attachment['url'] = $baseUrl . $attachment['path'];
                    $attachment['attachment_class'] = 'sp-attachment-id'.$attachment['attachment_id'];
                    $attachment['hash_class'] = 'sp-attachment-hash'.$attachment['attachment_id'] ;
                }
            }

            return $attachments;
        }

        return [];
    }
}
