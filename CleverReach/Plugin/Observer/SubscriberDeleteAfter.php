<?php

namespace CleverReach\Plugin\Observer;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\UnsubscribeReceiverTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SubscriberDeleteAfter implements ObserverInterface
{
    /**
     * Enqueue UnsubscribeReceiverTask when customer is deleted in shop.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $email = $observer->getEvent()->getSubscriber()->getEmail();
        try {
            $this->getQueueService()->enqueue('syncQueue', new UnsubscribeReceiverTask($email));
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
