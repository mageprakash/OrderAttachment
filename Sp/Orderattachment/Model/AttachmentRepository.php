<?php
namespace Sp\Orderattachment\Model;

use Sp\Orderattachment\Api\Data;
use Sp\Orderattachment\Api\AttachmentRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Sp\Orderattachment\Model\ResourceModel\Attachment as ResourceAttachment;
use Sp\Orderattachment\Model\ResourceModel\Attachment\CollectionFactory as AttachmentCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class AttachmentRepository implements AttachmentRepositoryInterface
{
    /**
     * @var ResourceAttachment
     */
    protected $resource;

    /**
     * @var AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $attachmentCollectionFactory;

    /**
     * @var Data\BlockSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Cms\Api\Data\BlockInterfaceFactory
     */
    protected $dataAttachmentFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceBlock $resource
     * @param BlockFactory $attachmentFactory
     * @param Data\BlockInterfaceFactory $dataAttachmentFactory
     * @param BlockCollectionFactory $attachmentCollectionFactory
     * @param Data\BlockSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceAttachment $resource,
        AttachmentFactory $attachmentFactory,
        \Sp\Orderattachment\Api\Data\AttachmentInterfaceFactory $dataAttachmentFactory,
        AttachmentCollectionFactory $attachmentCollectionFactory,
        Data\AttachmentSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->blockFactory = $attachmentFactory;
        $this->blockCollectionFactory = $attachmentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBlockFactory = $dataAttachmentFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    public function save(Data\AttachmentInterface $attachment)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $attachment->setStoreId($storeId);
        try {
            $this->resource->save($attachment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $attachment;
    }

    public function getById($attachmentId)
    {
        $attachment = $this->blockFactory->create();
        $this->resource->load($attachment, $attachmentId);
        if (!$attachment->getId()) {
            throw new NoSuchEntityException(__('CMS Block with id "%1" does not exist.', $attachmentId));
        }
        return $attachment;
    }

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->blockCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $blocks = [];
        /** @var Block $blockModel */
        foreach ($collection as $blockModel) {
            $blockData = $this->dataBlockFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $blockData,
                $blockModel->getData(),
                'Sp\Orderattachment\Api\Data\AttachmentInterface'
            );
            $blocks[] = $this->dataObjectProcessor->buildOutputDataArray(
                $blockData,
                'Sp\Orderattachment\Api\Data\AttachmentInterface'
            );
        }
        $searchResults->setItems($blocks);
        return $searchResults;
    }

    public function delete(Data\AttachmentInterface $attachment)
    {
        try {
            $this->resource->delete($attachment);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById($attachmentId)
    {
        return $this->delete($this->getById($attachmentId));
    }
}
