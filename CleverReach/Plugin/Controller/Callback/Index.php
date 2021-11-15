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
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\Plugin\Services\BusinessLogic\Authorization\AuthorizationService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
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
     * Callback index constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $pageFactory
    )
    {
        parent::__construct($context);

        Bootstrap::init();

        $this->_pageFactory = $pageFactory;
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
            $this->getWakeup()->wakeup();

            return $this->_pageFactory->create();
        } catch (FailedToRefreshAccessToken | FailedToRetrieveAuthInfoException | HttpCommunicationException
        | HttpRequestException | QueueStorageUnavailableException | QueryFilterInvalidParamException $e) {
            echo $e->getMessage();

            return '';
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

    /**
     * @return QueueService
     */
    private function getQueueService(): QueueService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }

    /**
     * @return TaskRunnerWakeup
     */
    private function getWakeup(): TaskRunnerWakeup
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
    }
}
