<?php

namespace CleverReach\Plugin\Services\BusinessLogic;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Configuration\Configuration;
use Magento\Framework\App\ObjectManager;

class ConfigurationService extends Configuration
{
    /**
     * ConfigurationService constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns system URL.
     *
     * @return string
     */
    public function getSystemUrl(): string
    {
        $objectManager = ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        return $storeManager->getStore()->getBaseUrl();
    }

    /**
     * Returns URL to async process controller.
     *
     * @param string $guid
     *
     * @return string
     */
    public function getAsyncProcessUrl($guid): string
    {
        return $this->getSystemUrl() . 'front/asyncprocess/index?guid=' . $guid;
    }

    /**
     * Returns default queue name.
     *
     * @return string
     */
    public function getDefaultQueueName(): string
    {
        return "defaultQueue";
    }

    /**
     * Returns CleverReach client ID.
     *
     * @return string
     */
    public function getClientId(): string
    {
        return 'CFkMVkzRPM';
    }

    /**
     * Return integration name.
     *
     * @return string
     */
    public function getIntegrationName(): string
    {
        return "Magento";
    }

    /**
     * Returns CleverReach client secret.
     *
     * @return string
     */
    public function getClientSecret(): string
    {
        return 'SNfWYY6lkdgxevBzCuq752MqOHKozzar';
    }
}
