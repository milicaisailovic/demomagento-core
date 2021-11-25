<?php

namespace CleverReach\Plugin\Observer;

use CleverReach\Plugin\Bootstrap;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ServiceInit implements ObserverInterface
{
    /**
     * Call initialization of services before every action.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        Bootstrap::init();
    }
}