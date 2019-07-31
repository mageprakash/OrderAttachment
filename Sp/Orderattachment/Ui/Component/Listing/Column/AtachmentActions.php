<?php
namespace Sp\Orderattachment\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class AtachmentActions extends Column
{
    protected $urlBuilder;

    protected $storeManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Url $urlBuilder,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['attachment_id'])) {
                    $download = $this->urlBuilder->getUrl(
                        'orderattachment/attachment/preview',
                        [
                            'attachment' => $item['attachment_id'],
                            'hash' => $item['hash'],
                            'download' => 1
                        ]
                    );
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $download,
                            'label' => __('Download')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
