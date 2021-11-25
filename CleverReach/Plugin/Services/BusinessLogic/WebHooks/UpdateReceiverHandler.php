<?php

namespace CleverReach\Plugin\Services\BusinessLogic\WebHooks;

class UpdateReceiverHandler extends ReceiverWebhookHandler
{
    /**
     * Handle receiver update on API.
     *
     * @param int $receiverId
     */
    public function handle(int $receiverId)
    {
        $receiver = $this->getReceiverFromApi($receiverId);
        $this->subscriberRepository->updateSubscriberStatus($receiver);
    }
}