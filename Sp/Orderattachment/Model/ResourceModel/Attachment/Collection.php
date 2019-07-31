<?php

namespace Sp\Orderattachment\Model\ResourceModel\Attachment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Sp\Orderattachment\Model\Attachment', 'Sp\Orderattachment\Model\ResourceModel\Attachment');
    }
}
