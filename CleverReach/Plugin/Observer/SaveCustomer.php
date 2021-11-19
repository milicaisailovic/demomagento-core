<?php

namespace CleverReach\Plugin\Observer;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\ReceiverSyncTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\Plugin\Services\BusinessLogic\Config\CleverReachConfig;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\CustomerService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SubscriberService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveCustomer implements ObserverInterface
{
    /**
     * SaveCustomer observer constructor.
     */
    public function __construct()
    {
        Bootstrap::init();
    }

    /**
     * Save new or edited customer on API.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $services = [CustomerService::class];

        $isSubscribed = $customer->getExtensionAttributes()->getIsSubscribed();
        if ($isSubscribed === true) {
            $services[] = SubscriberService::class;
        }

        CleverReachConfig::setSynchronizationServices($services);
        $task = new ReceiverSyncTask(new SyncConfiguration([$customer->getEmail()]));
        try {
            $this->getQueueService()->enqueue('authQueue', $task);
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
