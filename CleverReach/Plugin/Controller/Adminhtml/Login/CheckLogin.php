<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Login;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Authorization\AuthorizationService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class CheckLogin extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonResponseFactory;

    public function __construct(
        Context     $context,
        JsonFactory $jsonResponseFactory
    )
    {
        parent::__construct($context);

        Bootstrap::init();

        $this->jsonResponseFactory = $jsonResponseFactory;
    }

    public function execute()
    {
        $response = $this->jsonResponseFactory->create();

        try {
            $this->getAuthorizationService()->getAuthInfo();

            return $response->setData([$this->getUrl('cleverreach/dashboard/index')]);

        } catch (FailedToRefreshAccessToken | FailedToRetrieveAuthInfoException | QueryFilterInvalidParamException $e) {
            return $response->setData([]);
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
