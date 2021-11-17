<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Language\TranslationService as BaseTranslationService;
use Magento\Framework\App\ObjectManager;

class TranslationService extends BaseTranslationService
{
    /**
     * Returns current system language
     *
     * @return string
     */
    public function getSystemLanguage(): string
    {
        $objectManager = ObjectManager::getInstance();
        $store = $objectManager->get('Magento\Store\Api\Data\StoreInterface');

        return $store->getLocaleCode();
    }
}
