<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Authorization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\AuthorizationService as AuthorizationServiceCore;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\AuthProxy;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Authorization\Contracts\AuthorizationServiceInterface;
use Magento\Framework\App\ObjectManager;

class AuthorizationService extends AuthorizationServiceCore implements AuthorizationServiceInterface
{
    /**
     * Retrieves authorization redirect url.
     *
     * @param bool $isRefresh Specifies whether url is retrieved for token refresh.
     *
     * @return string Authorization redirect url.
     */
    public function getRedirectURL($isRefresh = false): string
    {
        $objectManager = ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        return $storeManager->getStore()->getBaseUrl() . 'front/callback/index';
    }

    /**
     * Call proxy for verification.
     *
     * @param string $code
     *
     * @return void
     *
     * @throws FailedToRefreshAccessToken
     * @throws FailedToRetrieveAuthInfoException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function authorize(string $code): void
    {
        $authInfo = $this->getAuthProxy()->getAuthInfo($code, $this->getRedirectURL());
        $this->setAuthInfo($authInfo);
    }

    /**
     * @return AuthProxy
     */
    private function getAuthProxy(): AuthProxy
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(AuthProxy::CLASS_NAME);
    }
}
