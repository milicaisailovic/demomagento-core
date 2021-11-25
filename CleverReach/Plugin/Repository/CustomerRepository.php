<?php

namespace CleverReach\Plugin\Repository;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class CustomerRepository
{
    /**
     * @var CollectionFactory
     */
    private $customerFactory;

    /**
     * CustomerRepository constructor.
     */
    public function __construct(
        CollectionFactory $customerFactory
    )
    {
        $this->customerFactory = $customerFactory;
    }

    /**
     * Get batch of customers from database based on array of emails.
     *
     * @param array $emails
     *
     * @return array
     */
    public function getBatchOfCustomers(array $emails): array
    {
        $customerCollection = $this->customerFactory->create();
        $customerCollection->addFieldToFilter('email', array('in' => $emails));

        return $customerCollection->getData();
    }

    /**
     * Return all customer emails.
     *
     * @return array
     */
    public function getEmails(): array
    {
        $customerCollection = $this->customerFactory->create();

        return $customerCollection->getColumnValues('email');
    }
}
