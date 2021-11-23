<?php

namespace CleverReach\Plugin\Model;

use CleverReach\Plugin\ResourceModel\CleverReachEntity as CleverReachModel;
use Magento\Framework\Model\AbstractModel;

class CleverReachEntity extends AbstractModel
{
    /**
     * Model initialization.
     */
    protected function _construct()
    {
        $this->_init(CleverReachModel::class);
    }
}
