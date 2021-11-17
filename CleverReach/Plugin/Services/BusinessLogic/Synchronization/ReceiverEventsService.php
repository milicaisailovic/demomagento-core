<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService as BaseReceiverEventsService;

class ReceiverEventsService extends BaseReceiverEventsService
{
    /**
     * ReceiverEventsService constructor.
     */
    public function __construct()
    {
    }

    /**
     * Provides url that will listen to web hook requests.
     *
     * @return string
     */
    public function getEventUrl(): string
    {
        return 'https://3c2c-82-117-217-138.ngrok.io/';
    }
}
