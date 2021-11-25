<?php

namespace CleverReach\Plugin\Controller\Callback;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite\ConnectTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Authorization\AuthorizationService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
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

        $this->_pageFactory = $pageFactory;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Call authorization service and enqueue ConnectTask.
     *
     * @return Json|Page
     */
    public function execute()
    {
        try {
            $this->getAuthorizationService()->authorize($_GET['code']);
            $this->getQueueService()->enqueue('authQueue', new ConnectTask());

        } catch (BaseException $e) {
            Logger::logError('Callback\Index controller. ' . $e->getMessage());
            $response = $this->jsonFactory->create();

            return $response->setData($e->getMessage());
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
