<?php

namespace CleverReach\Plugin\Repository;


use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;

class SubscriberRepository
{
    /**
     * @var CollectionFactory
     */
    private $subscriberFactory;

    /**
     * SubscriberRepository constructor.
     */
    public function __construct(
        CollectionFactory $subscriberFactory
    )
    {
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Get all subscriber emails.
     *
     * @return array
     */
    public function getEmails(): array
    {
        $customerCollection = $this->subscriberFactory->create();

        return $customerCollection->getColumnValues('subscriber_email');
    }

    /**
     * Get subscriber with forwarded email.
     *
     * @param string $email
     *
     * @return array|null
     */
    public function getSubscriberByEmail(string $email): ?array
    {
        $customerCollection = $this->subscriberFactory->create();
        $customerCollection->addFieldToFilter('subscriber_email', ['eq' => $email]);

        return $customerCollection->getData();
    }
}
