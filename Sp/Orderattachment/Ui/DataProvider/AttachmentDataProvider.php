<?php
namespace Sp\Orderattachment\Ui\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Sp\Orderattachment\Model\ResourceModel\Attachment\Grid\CollectionFactory;
use Sp\Orderattachment\Model\ResourceModel\Attachment\Grid\Collection;

class AttachmentDataProvider extends AbstractDataProvider
{

    protected $collectionFactory;

    protected $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $arrItems['items'][] = $item->toArray([]);
        }

        return $arrItems;
    }
}
