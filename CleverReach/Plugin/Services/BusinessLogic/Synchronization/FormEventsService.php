<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\FormEventsService as BaseFormEventsService;

class FormEventsService extends BaseFormEventsService
{
    /**
     * FormEventsService constructor.
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
        return 'https://7cb3-82-117-217-138.ngrok.io';
    }
}
