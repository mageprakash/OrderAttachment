<?php
namespace Sp\Orderattachment\Model;

use Sp\Orderattachment\Api\Data\AttachmentInterface as AttachmentInt;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Attachment extends AbstractModel implements AttachmentInt, IdentityInterface
{
    /**
     * XML configuration paths for "Allow file upload during checkout" property
     */
    const XML_PATH_ATTACHMENT_ON_ATTACHMENT_INFORMATION = 'orderattachments/general/attachment_information';

    /**
     * XML configuration paths for "Allow file upload during checkout" property
     */
    const XML_PATH_ATTACHMENT_ON_DISPLAY_ATTACHMENT = 'orderattachments/general/display_attachment';

    /**
     * XML configuration paths for "File restrictions - limit" property
     */
    const XML_PATH_ATTACHMENT_FILE_LIMIT = 'orderattachments/general/count';
    /**
     * XML configuration paths for "File restrictions - size" property
     */
    const XML_PATH_ATTACHMENT_FILE_SIZE = 'orderattachments/general/size';

    /**
     * XML configuration paths for "File restrictions - Allowed extensions" property
     */
    const XML_PATH_ATTACHMENT_FILE_EXT = 'orderattachments/general/extension';
    /**
     * XML configuration paths for "Enabled orderattachment module" property
     */
    const XML_PATH_ENABLE_ATTACHMENT = 'orderattachments/general/enabled';
    /**
     * XML configuration paths for "Allow file upload during checkout" property
     */
    const XML_PATH_ATTACHMENT_ON_ATTACHMENT_TITLE = 'orderattachments/general/attachment_title';
    /**
     * XML configuration paths for "Allow file upload during checkout" property
     */
    const DEFAULT_TITLE_ATTACHMENT = 'Order Attachment';
    /**
     * cache tag
     */
    const CACHE_TAG = 'orderattachment_attachment';

    /**
     * @var string
     */
    protected $_cacheTag = 'orderattachment_attachment';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'orderattachment_attachment';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Sp\Orderattachment\Model\ResourceModel\Attachment');
    }

    public function getOrderAttachments($orderId)
    {
        return $this->_getResource()->getOrderAttachments($orderId);
    }

    public function getAttachmentsByQuote($quoteId)
    {
        return $this->_getResource()->getAttachmentsByQuote($quoteId);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get attachment_id
     *
     * return int
     */
    public function getAttachmentId()
    {
        return $this->getData(self::ATTACHMENT_ID);
    }

    /**
     * Get quote_id
     *
     * return string
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Get order_id
     *
     * return string
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Get path
     *
     * return int
     */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * Get Comment
     *
     * return int
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * Get HASH
     *
     * return string
     */
    public function getHash()
    {
        return $this->getData(self::HASH);
    }

    /**
     * Get TYPE
     *
     * return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Get Uploaded
     *
     * return string
     */
    public function getUploadedAt()
    {
        return $this->getData(self::UPLOADED_AT);
    }

    /**
     * Get Modified
     *
     * return string
     */
    public function getModifiedAt()
    {
        return $this->getData(self::MODIFIED_AT);
    }

    public function setAttachmentId($AttachmentId)
    {
        return $this->setData(self::ATTACHMENT_ID, $AttachmentId);
    }

    public function setQuoteId($QuoteId)
    {
        return $this->setData(self::QUOTE_ID, $QuoteId);
    }

    public function setOrderId($OrderId)
    {
        return $this->setData(self::ORDER_ID, $OrderId);
    }

    public function setPath($Path)
    {
        return $this->setData(self::PATH, $Path);
    }

    public function setComment($Comment)
    {
        return $this->setData(self::COMMENT, $Comment);
    }

    public function setHash($Hash)
    {
        return $this->setData(self::HASH, $Hash);
    }

    public function setType($Type)
    {
        return $this->setData(self::TYPE, $Type);
    }

    public function setUploadedAt($UploadedAt)
    {
        return $this->setData(self::UPLOADED_AT, $UploadedAt);
    }

    public function setModifiedAt($ModifiedAt)
    {
        return $this->setData(self::MODIFIED_AT, $ModifiedAt);
    }
}
