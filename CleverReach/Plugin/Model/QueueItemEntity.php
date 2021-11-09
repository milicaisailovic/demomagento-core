<?php

namespace CleverReach\Plugin\Model;

use CleverReach\Plugin\ResourceModel\QueueItemEntity as QueueItemResourceModel;

class QueueItemEntity extends CleverReachEntity
{
    /**
     * Model initialization.
     */
    protected function _construct()
    {
        $this->_init(QueueItemResourceModel::class);
    }
}