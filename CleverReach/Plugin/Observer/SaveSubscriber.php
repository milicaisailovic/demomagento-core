<?php

namespace CleverReach\Plugin\Observer;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\SubscribeReceiverTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\UnsubscribeReceiverTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\Plugin\Services\BusinessLogic\Config\CleverReachConfig;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SubscriberService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveSubscriber implements ObserverInterface
{
    /**
     * SaveSubscriber constructor.
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
        $subscriber = $observer->getEvent()->getSubscriber();
        CleverReachConfig::setSynchronizationServices([SubscriberService::class]);
        $task = $subscriber->getSubscriberStatus() === 1 ? new SubscribeReceiverTask($subscriber->getEmail())
            : new UnsubscribeReceiverTask($subscriber->getEmail());
        try {
            $this->getQueueService()->enqueue('authQueue', $task);
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
}
