<?php
namespace Sp\Orderattachment\Controller\Adminhtml\Attachment;

class Upload extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Sp_Orderattachment::upload';

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Sp\Orderattachment\Helper\Attachment
     */
    protected $attachmentHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Sp\Orderattachment\Helper\Attachment $attachmentHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Sp\Orderattachment\Helper\Attachment $attachmentHelper
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->attachmentHelper = $attachmentHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $result = $this->attachmentHelper->saveAttachment($this->getRequest());

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create()
            ->setHeader('Content-type', 'text/plain')
            ->setContents(json_encode($result));

        return $response;
    }
}
