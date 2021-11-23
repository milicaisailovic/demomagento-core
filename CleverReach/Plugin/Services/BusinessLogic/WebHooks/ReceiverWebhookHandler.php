<?php

namespace CleverReach\Plugin\Services\BusinessLogic\WebHooks;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Group\Contracts\GroupService as BaseGroupService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Repository\SubscriberRepository;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\GroupService;
use Magento\Framework\App\ObjectManager;

class ReceiverWebhookHandler
{
    /**
     * @var SubscriberRepository
     */
    private $subscriberRepository;

    /**
     * ReceiverWebhookHandler constructor.
     */
    public function __construct()
    {
        $manager = ObjectManager::getInstance();
        $this->subscriberRepository = $manager->get(SubscriberRepository::class);
    }

    /**
     * Handle receiver created on API.
     *
     * @param int $receiverId
     *
     * @return bool
     */
    public function receiverCreated(int $receiverId): bool
    {
        $receiver = $this->getReceiverFromApi($receiverId);
        if ($receiver === null) {
            return false;
        }

        $this->subscriberRepository->saveSubscriber($receiver->getEmail());

        return true;
    }

    /**
     * Handle receiver update on API.
     *
     * @param int $receiverId
     *
     * @return bool
     */
    public function receiverUpdated(int $receiverId): bool
    {
        $receiver = $this->getReceiverFromApi($receiverId);
        $this->subscriberRepository->updateSubscriberStatus($receiver);

        return true;
    }

    /**
     * Handle receiver subscribed on API.
     *
     * @param int $receiverId
     */
    public function receiverSubscribed(int $receiverId): void
    {
        $this->receiverCreated($receiverId);
    }

    /**
     * Handle receiver unsubscribed on API.
     *
     * @param int $receiverId
     */
    public function receiverUnsubscribed(int $receiverId): void
    {
        $receiver = $this->getReceiverFromApi($receiverId);
        $this->subscriberRepository->unsubscribe($receiver->getEmail());
    }

    /**
     * @param int $receiverId
     *
     * @return Receiver|null
     */
    private function getReceiverFromApi(int $receiverId): ?Receiver
    {
        $groupId = $this->getGroupService()->getId();
        try {
            return $this->getReceiverProxy()->getReceiver($groupId, $receiverId);
        } catch (FailedToRefreshAccessToken | FailedToRetrieveAuthInfoException | HttpCommunicationException | HttpRequestException $e) {
            return null;
        }
    }

    /**
     * @return Proxy
     */
    private function getReceiverProxy(): Proxy
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(Proxy::CLASS_NAME);
    }

    /**
     * @return GroupService
     */
    private function getGroupService(): GroupService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(BaseGroupService::CLASS_NAME);
    }
}
