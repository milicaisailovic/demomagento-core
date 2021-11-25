<?php

namespace CleverReach\Plugin\Block;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceAlias;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\Plugin\Services\BusinessLogic\Config\CleverReachConfig;
use CleverReach\Plugin\Services\BusinessLogic\ConfigurationService;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;

class LoginBlock extends Template
{
    /**
     * LoginBlock constructor.
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
     * Return login page URL.
     *
     * @return string
     */
    public function getLoginPageUrl(): string
    {
        $redirectUrl = $this->getAuthorizationService()->getRedirectURL();

        return CleverReachConfig::AUTHORIZE_URL . '?client_id=' . $this->getConfigService()->getClientId()
            . '&grant=basic&response_type=code&redirect_uri=' . urlencode($redirectUrl);
    }

    /**
     * Get URL for checking if login is done.
     *
     * @return string
     */
    public function checkLoginUrl(): string
    {
        return $this->getUrl('cleverreach/login/checklogin');
    }

    /**
     * @return AuthorizationService
     */
    private function getAuthorizationService(): AuthorizationService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(AuthorizationServiceAlias::CLASS_NAME);
    }

    /**
     * @return ConfigurationService
     */
    private function getConfigService(): ConfigurationService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(Configuration::CLASS_NAME);
    }
}
