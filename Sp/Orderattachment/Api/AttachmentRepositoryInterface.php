<?php
namespace Sp\Orderattachment\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface AttachmentRepositoryInterface
{

    public function save(Data\AttachmentInterface $attachment);

    public function getById($attachmentId);

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    public function delete(Data\AttachmentInterface $attachment);

    public function deleteById($attachmentId);
}
