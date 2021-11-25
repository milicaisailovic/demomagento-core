<?php

namespace CleverReach\Plugin\Repository;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

class SubscriberRepository
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * SubscriberRepository constructor.
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        SubscriberFactory $subscriberFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Get all subscriber emails.
     *
     * @return array
     */
    public function getEmails(): array
    {
        $customerCollection = $this->collectionFactory->create();

        return $customerCollection->getColumnValues('subscriber_email');
    }

    /**
     * Get batch of subscribers from database based on array of emails.
     *
     * @param array $emails
     * @return array
     */
    public function getBatchOfSubscribers(array $emails): array
    {
        $customerCollection = $this->collectionFactory->create();
        $customerCollection->addFieldToFilter('subscriber_email', array('in' => $emails));

        return $customerCollection->getData();
    }

    /**
     * Save subscriber in database.
     *
     * @param string $email
     */
    public function saveSubscriber(string $email)
    {
        $this->subscriberFactory->create()->subscribe($email);
    }

    /**
     * Update subscriber status.
     *
     * @param Receiver $receiver
     */
    public function updateSubscriberStatus(Receiver $receiver)
    {
        $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($receiver->getEmail(), 1);
        if ($receiver->isActive() && $subscriber->getStatus() !== Subscriber::STATUS_SUBSCRIBED) {
            $subscriber->subscribe($receiver->getEmail());
        }

        if (!$receiver->isActive() && $subscriber->getStatus() !== Subscriber::STATUS_UNSUBSCRIBED) {
            $subscriber->unsubscribe();
        }
    }

    /**
     * Unsubscribe receiver.
     *
     * @param string $email
     */
    public function unsubscribe(string $email)
    {
        try {
            $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($email, 1);
            $subscriber->unsubscribe();
        } catch (LocalizedException $e) {
        }
    }
}
