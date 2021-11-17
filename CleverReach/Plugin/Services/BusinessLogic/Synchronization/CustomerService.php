<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\ReceiverService;
use CleverReach\Plugin\Repository\CustomerRepository;
use Magento\Framework\App\ObjectManager;

class CustomerService extends ReceiverService
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * CustomerService constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $objectManager = ObjectManager::getInstance();
        $this->customerRepository = $objectManager->create(CustomerRepository::class);
    }

    /**
     * Retrieves receiver from the integrated system.
     *
     * @param string $email Receiver identifer.
     *
     * @param bool $isServiceSpecificDataRequired
     *
     * @return Receiver | null
     */
    public function getReceiver($email, $isServiceSpecificDataRequired = false): ?Receiver
    {
        return null;
    }

    /**
     * Retrieves a batch of receivers.
     *
     * @param string[] $emails List of customer emails used for retrieval.
     * @param bool $isServiceSpecificDataRequired Specifies whether service should provide service specific data.
     *
     * @return Receiver[]
     */
    public function getReceiverBatch(array $emails, $isServiceSpecificDataRequired = false): array
    {
        $receivers = [];
        foreach ($emails as $email) {
            $rawCustomer = $this->customerRepository->getCustomerByEmail($email);
            $receiver = (new Receiver())->fromArray($this->convertToReceiver($rawCustomer[0]));
            $receivers[] = $receiver;
        }

        return $receivers;
    }

    /**
     * Retrieves list of customer emails provided by the integration.
     *
     * @return string[]
     */
    public function getReceiverEmails(): array
    {
        return $this->customerRepository->getEmails();
    }

    /**
     * @param array $customer
     *
     * @return array
     */
    private function convertToReceiver(array $customer): array
    {
        $receiver = [];
        $receiver['email'] = $customer['email'];
        $receiver['registered'] = $customer['created_at'];
        $receiver['deactivated'] = $customer['created_at'];
        $receiver['global_attributes']['firstname'] = $customer['firstname'];
        $receiver['global_attributes']['lastname'] = $customer['lastname'];

        return $receiver;
    }
}
