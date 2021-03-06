<?php

namespace CleverReach\Plugin\Block;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceContract;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Authorization\AuthorizationService;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;

class DashboardBlock extends Template
{
    /**
     * DashboardBlock constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array   $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * Returns CleverReach client ID.
     *
     * @return string
     */
    public function getClientId(): string
    {
        try {
            return $this->getAuthorizationService()->getUserInfo()->getId();
        } catch (BaseException $e) {
            return '0';
        }
    }

    /**
     * Get URL for starting initial synchronization.
     *
     * @return string
     */
    public function getSynchronizationUrl(): string
    {
        return $this->getUrl('cleverreach/dashboard/synchronization');
    }

    /**
     * Returns status of the latest task of type InitialSyncTask.
     *
     * @return string
     */
    public function getSyncStatusUrl(): string
    {
        return $this->getUrl('cleverreach/dashboard/checksyncstatus');
    }

    /**
     * Get URL for manual synchronization.
     *
     * @return string
     */
    public function getManualSynchronizationUrl(): string
    {
        return $this->getUrl('cleverreach/dashboard/manualsynchronization');

    }

    /**
     * @return AuthorizationService
     */
    private function getAuthorizationService(): AuthorizationService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(AuthorizationServiceContract::CLASS_NAME);
    }
}
