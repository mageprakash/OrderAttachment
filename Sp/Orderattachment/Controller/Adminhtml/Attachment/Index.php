<?php
namespace Sp\Orderattachment\Controller\Adminhtml\Attachment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Sp_Orderattachment::orderattachment_attachment';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Sp_Orderattachment::orderattachment_attachment');
        $resultPage->addBreadcrumb(__('OrderAttachment'), __('OrderAttachment'));
        $resultPage->addBreadcrumb(__('Attachments'), __('Attachments'));
        $resultPage->getConfig()->getTitle()->prepend(__('Order Attachments'));

        return $resultPage;
    }
}
