<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Dashboard;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService as SyncConfigServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\SyncConfigService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\CustomerService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SubscriberService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject\IdentityService;

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

        $identityService = new IdentityService();
        $services = [
            new SyncService($identityService->generateId(), 2, CustomerService::class),
            new SyncService($identityService->generateId(), 1, SubscriberService::class)
        ];

        $configService = $this->getSyncConfigService();
        $configService->setEnabledServices($services);

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

    /**
     * @return SyncConfigService
     */
    private function getSyncConfigService(): SyncConfigService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(SyncConfigServiceContract::CLASS_NAME);
    }
}


