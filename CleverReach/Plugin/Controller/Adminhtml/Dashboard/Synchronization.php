<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Dashboard;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\Plugin\Services\BusinessLogic\Config\CleverReachConfig;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\CustomerService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SubscriberService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Synchronization extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonResponseFactory;

    /**
     * Synchronization constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     */
    public function __construct(Context $context, JsonFactory $jsonFactory)
    {
        parent::__construct($context);

        Bootstrap::init();

        $this->jsonResponseFactory = $jsonFactory;
    }

    /**
     * Enqueue InitialSyncTask.
     *
     * @return Json
     */
    public function execute(): Json
    {
        $response = $this->jsonResponseFactory->create();

        CleverReachConfig::setSynchronizationServices([CustomerService::class, SubscriberService::class]);

        try {
            $this->getQueueService()->enqueue('authQueue', new InitialSyncTask());
            $this->getWakeup()->wakeup();

        } catch (QueueStorageUnavailableException $e) {
        }

        return $response->setData([]);
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