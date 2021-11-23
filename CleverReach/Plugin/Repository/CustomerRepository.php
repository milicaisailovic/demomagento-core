<?php

namespace CleverReach\Plugin\Repository;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class CustomerRepository
{
    /**
     * @var CollectionFactory
     */
    private $customerFactory;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * CustomerRepository constructor.
     */
    public function __construct(
        CollectionFactory $customerFactory,
        Customer          $customer
    )
    {
        $this->customerFactory = $customerFactory;
        $this->customer = $customer;
    }

    /**
     * Get customer from database by email.
     *
     * @param string $email
     *
     * @return array
     */
    public function getCustomerByEmail(string $email): array
    {
        $customerCollection = $this->customerFactory->create();
        $customerCollection->addFieldToFilter('email', ['eq' => $email]);

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
