<?php

namespace CleverReach\Plugin\Services\BusinessLogic\WebHooks;

class UnsubscribeReceiverHandler extends ReceiverWebhookHandler
{
    /**
     * Handle receiver unsubscribed on API.
     *
     * @param int $receiverId
     */
    public function handle(int $receiverId): void
    {
        $receiver = $this->getReceiverFromApi($receiverId);
        $this->subscriberRepository->unsubscribe($receiver->getEmail());
    }
}