<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Config;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService as SyncConfigServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\SyncConfigService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use Magento\Framework\DataObject\IdentityService;

class CleverReachConfig
{
    const MENU_ID = 'CleverReach_Plugin::cleverreach_landingpage';

    const AUTHORIZE_URL = 'https://rest.cleverreach.com/oauth/authorize.php';

    /**
     * Set synchronization services.
     *
     * @param array $serviceNames sorted by priority!
     */
    public static function setSynchronizationServices(array $serviceNames)
    {
        $identityService = new IdentityService();
        $services = [];
        $currentPriority = 1;
        foreach ($serviceNames as $service) {
            $services[] = new SyncService($identityService->generateId(), $currentPriority++, $service);
        }

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
