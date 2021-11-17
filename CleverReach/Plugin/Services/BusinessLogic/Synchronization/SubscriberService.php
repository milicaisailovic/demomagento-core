<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\ReceiverService;
use CleverReach\Plugin\Repository\SubscriberRepository;
use Magento\Framework\App\ObjectManager;

class SubscriberService extends ReceiverService
{
    /**
     * @var SubscriberRepository
     */
    private $subscriberRepository;

    /**
     * SubscriberService constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $objectManager = ObjectManager::getInstance();
        $this->subscriberRepository = $objectManager->create(SubscriberRepository::class);
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
     * @param string[] $emails List of subscriber emails used for retrieval.
     * @param bool $isServiceSpecificDataRequired Specifies whether service should provide service specific data.
     *
     * @return Receiver[]
     */
    public function getReceiverBatch(array $emails, $isServiceSpecificDataRequired = false): array
    {
        $receivers = [];
        foreach ($emails as $email) {
            $rawSubscriber = $this->subscriberRepository->getSubscriberByEmail($email);
            $receiver = (new Receiver())->fromArray($this->convertToReceiver($rawSubscriber[0]));
            $receiver->setActive(true);
            $receiver->setDeactivated('0');
            $receivers[] = $receiver;
        }

        return $receivers;
    }

    /**
     * Retrieves list of subscriber emails provided by the integration.
     *
     * @return string[]
     */
    public function getReceiverEmails(): array
    {
        return $this->subscriberRepository->getEmails();
    }

    /**
     * @param array $subscriber
     *
     * @return array
     */
    private function convertToReceiver(array $subscriber): array
    {
        $receiver = [];
        $receiver['email'] = $subscriber['subscriber_email'];
        $receiver['registered'] = $subscriber['change_status_at'];
        $receiver['activated'] = $subscriber['change_status_at'];
        $receiver['active'] = true;

        return $receiver;
    }
}
