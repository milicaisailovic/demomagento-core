<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Config;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService as SyncConfigServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\SyncConfigService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\CustomerService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SubscriberService;
use Magento\Framework\DataObject\IdentityService;

class CleverReachConfig
{
    const MENU_ID = 'CleverReach_Plugin::cleverreach_landingpage';

    const AUTHORIZE_URL = 'https://rest.cleverreach.com/oauth/authorize.php';

    public static function setSynchronizationServices()
    {
        $identityService = new IdentityService();
        $services = [
            new SyncService($identityService->generateId(), 1, SubscriberService::class),
            new SyncService($identityService->generateId(), 2, CustomerService::class)
        ];

        $configService = self::getSyncConfigService();
        $configService->setEnabledServices($services);
    }

    /**
     * @return SyncConfigService
     */
    private static function getSyncConfigService(): SyncConfigService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(SyncConfigServiceContract::CLASS_NAME);
    }
}
