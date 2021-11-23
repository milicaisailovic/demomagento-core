<?php

namespace CleverReach\Plugin\Controller\Callback;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite\ConnectTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\Plugin\Services\BusinessLogic\Authorization\AuthorizationService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * Callback index constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory
    )
    {
        parent::__construct($context);

        Bootstrap::init();

        $this->_pageFactory = $pageFactory;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Call authorization service and enqueue ConnectTask.
     *
     * @return Page|ResultInterface|string
     */
    public function execute()
    {
        try {
            $this->getAuthorizationService()->authorize($_GET['code']);
            $this->getQueueService()->enqueue('authQueue', new ConnectTask());

        } catch (FailedToRefreshAccessToken | FailedToRetrieveAuthInfoException | HttpCommunicationException
        | HttpRequestException | QueueStorageUnavailableException | QueryFilterInvalidParamException $e) {
            $response = $this->jsonFactory->create();
            $response->setHttpResponseCode($e->getCode());

            return $response->setData($e);
        }

        return $this->_pageFactory->create();
    }

    /**
     * @return AuthorizationService
     */
    private function getAuthorizationService(): AuthorizationService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(AuthorizationServiceContract::CLASS_NAME);
    }

    /**
     * @return QueueService
     */
    private function getQueueService(): QueueService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }
}
