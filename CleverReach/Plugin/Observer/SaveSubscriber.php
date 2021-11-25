<?php

namespace CleverReach\Plugin\Observer;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\SubscribeReceiverTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\UnsubscribeReceiverTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveSubscriber implements ObserverInterface
{
    /**
     * Save new or edited customer on API.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        $task = $subscriber->getSubscriberStatus() === 1 ? new SubscribeReceiverTask($subscriber->getEmail())
            : new UnsubscribeReceiverTask($subscriber->getEmail());
        try {
            $this->getQueueService()->enqueue('syncQueue', $task);
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
