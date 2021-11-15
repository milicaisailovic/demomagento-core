<?php

namespace CleverReach\Plugin\Block;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
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

        Bootstrap::init();
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
        } catch (FailedToRetrieveUserInfoException | QueryFilterInvalidParamException $e) {
            return '0';
        }
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
