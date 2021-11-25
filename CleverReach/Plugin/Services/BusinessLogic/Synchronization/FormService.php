<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\FormService as BaseFormService;

class FormService extends BaseFormService
{
    /**
     * Retrieves the integration's default form name.
     *
     * @return string
     */
    public function getDefaultFormName(): string
    {
        return 'formName';
    }
}
