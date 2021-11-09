<?php

namespace CleverReach\Plugin\ResourceModel\QueueItemEntity;

use CleverReach\Plugin\Model\QueueItemEntity;
use CleverReach\Plugin\ResourceModel\CleverReachEntity\Collection as CleverReachEntityCollection;
use CleverReach\Plugin\ResourceModel\QueueItemEntity as QueueItemResourceModel;

class Collection extends CleverReachEntityCollection
{
    /**
     * Collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(QueueItemEntity::class, QueueItemResourceModel::class);
    }
}
