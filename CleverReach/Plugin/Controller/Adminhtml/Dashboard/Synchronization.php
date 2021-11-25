<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Dashboard;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
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

        try {
            $this->getQueueService()->enqueue('syncQueue', new InitialSyncTask());
        } catch (BaseException $e) {
            Logger::logError('Dashboard\Synchronization controller. ' . $e->getMessage());
            return $response->setData($e->getMessage());
        }

        return $response->setData(['success' => true]);
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