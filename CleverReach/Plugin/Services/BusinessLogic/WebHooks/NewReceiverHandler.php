<?php

namespace CleverReach\Plugin\Services\BusinessLogic\WebHooks;

class NewReceiverHandler extends ReceiverWebhookHandler
{
    /**
     * Handle receiver created on API.
     *
     * @param int $receiverId
     *
     * @return bool
     */
    public function handle(int $receiverId): bool
    {
        $receiver = $this->getReceiverFromApi($receiverId);
        if ($receiver === null) {
            return false;
        }

        $this->subscriberRepository->saveSubscriber($receiver->getEmail());

        return true;
    }
}