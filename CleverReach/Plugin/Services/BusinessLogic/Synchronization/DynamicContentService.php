<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\DynamicContent\DTO\DynamicContent;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\DynamicContent\DynamicContentService as BaseDynamicContentService;

class DynamicContentService extends BaseDynamicContentService
{
    /**
     * Return list of supported contents
     *
     * @return DynamicContent[]
     */
    public function getSupportedDynamicContent(): array
    {
        return [];
    }
}
