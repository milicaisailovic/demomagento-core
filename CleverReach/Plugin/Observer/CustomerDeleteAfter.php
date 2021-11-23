<?php

namespace CleverReach\Plugin\Observer;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\DeactivateReceiverTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\Plugin\Services\BusinessLogic\Config\CleverReachConfig;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\CustomerService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerDeleteAfter implements ObserverInterface
{
    /**
     * CustomerDeleteAfter constructor.
     */
    public function __construct()
    {
        Bootstrap::init();
    }

    /**
     * Deactivate receiver on API when customer is deleted in shop.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $email = $observer->getEvent()->getCustomer()->getEmail();
        CleverReachConfig::setSynchronizationServices([CustomerService::class]);
        try {
            $this->getQueueService()->enqueue('authQueue', new DeactivateReceiverTask($email));
            $this->getWakeup()->wakeup();
        } catch (QueueStorageUnavailableException $e) {
        }
    }

    /**
     * @return QueueService
     */
    private function getQueueService(): QueueService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }

    /**
     * @return TaskRunnerWakeup
     */
    private function getWakeup(): TaskRunnerWakeup
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
    }
}