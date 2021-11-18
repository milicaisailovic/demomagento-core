<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Dashboard;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class CheckInitialSyncStatus extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonResponseFactory;

    /**
     * CheckInitialSyncStatus constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonResponseFactory
     */
    public function __construct(
        Context     $context,
        JsonFactory $jsonResponseFactory
    )
    {
        parent::__construct($context);

        Bootstrap::init();

        $this->jsonResponseFactory = $jsonResponseFactory;
    }

    /**
     * Returns status of the latest task of type InitialSyncTask.
     *
     * @return Json
     */
    public function execute(): Json
    {
        $response = $this->jsonResponseFactory->create();
        $queueItem = $this->getQueueService()->findLatestByType('InitialSyncTask');
        if ($queueItem === null) {
            return $response->setData('error');
        }

        return $response->setData($queueItem->getStatus());
    }

    /**
     * @return \CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService
     */
    private function getQueueService(): QueueService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }
}
