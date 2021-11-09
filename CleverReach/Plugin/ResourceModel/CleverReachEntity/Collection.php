<?php

namespace CleverReach\Plugin\ResourceModel\CleverReachEntity;

use CleverReach\Plugin\Model\CleverReachEntity;
use CleverReach\Plugin\ResourceModel\CleverReachEntity as CleverReachResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Collection initialization.
     */
    protected function _construct()
    {
        $this->_init(CleverReachEntity::class, CleverReachResourceModel::class);
    }
}