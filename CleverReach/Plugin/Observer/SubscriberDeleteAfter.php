<?php

namespace CleverReach\Plugin\Observer;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\UnsubscribeReceiverTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\Plugin\Services\BusinessLogic\Config\CleverReachConfig;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\CustomerService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SubscriberDeleteAfter implements ObserverInterface
{
    /**
     * CustomerDeleteAfter constructor.
     */
    public function __construct()
    {
        Bootstrap::init();
    }

    /**
     * Enqueue UnsubscribeReceiverTask when customer is deleted in shop.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $email = $observer->getEvent()->getSubscriber()->getEmail();
        CleverReachConfig::setSynchronizationServices([CustomerService::class]);
        try {
            $this->getQueueService()->enqueue('authQueue', new UnsubscribeReceiverTask($email));
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
