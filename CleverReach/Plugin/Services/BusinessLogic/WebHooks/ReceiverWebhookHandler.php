<?php

namespace CleverReach\Plugin\Services\BusinessLogic\WebHooks;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Group\Contracts\GroupService as BaseGroupService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Repository\SubscriberRepository;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\GroupService;
use Magento\Framework\App\ObjectManager;

abstract class ReceiverWebhookHandler
{
    /**
     * @var SubscriberRepository
     */
    protected $subscriberRepository;

    /**
     * ReceiverWebhookHandler constructor.
     */
    public function __construct()
    {
        $manager = ObjectManager::getInstance();
        $this->subscriberRepository = $manager->get(SubscriberRepository::class);
    }

    /**
     *
     * @return void
     */
    abstract public function handle(int $receiverId);

    /**
     * @param int $receiverId
     *
     * @return Receiver|null
     */
    protected function getReceiverFromApi(int $receiverId): ?Receiver
    {
        $groupId = $this->getGroupService()->getId();
        try {
            return $this->getReceiverProxy()->getReceiver($groupId, $receiverId);
        } catch (BaseException $e) {
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