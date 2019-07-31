<?php
namespace Sp\Orderattachment\Api\Data;

interface AttachmentInterface
{
    CONST ATTACHMENT_ID = 'attachment_id';
    CONST QUOTE_ID = 'quote_id';
    CONST ORDER_ID = 'order_id';
    CONST PATH = 'path';
    CONST COMMENT = 'comment';
    CONST HASH = 'hash';
    CONST TYPE = 'type';
    CONST UPLOADED_AT = 'uploaded_at';
    CONST MODIFIED_AT = 'modified_at';

    public function getAttachmentId();

    public function getQuoteId();

    public function getOrderId();

    public function getPath();

    public function getComment();

    public function getHash();

    public function getType();

    public function getUploadedAt();

    public function getModifiedAt();

    public function setAttachmentId($AttachmentId);

    public function setQuoteId($QuoteId);

    public function setOrderId($OrderId);

    public function setPath($Path);

    public function setComment($Comment);

    public function setHash($Hash);

    public function setType($Type);

    public function setUploadedAt($UploadedAt);

    public function setModifiedAt($ModifiedAt);
}
