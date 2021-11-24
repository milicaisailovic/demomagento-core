<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Dashboard;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueService as BaseQueueService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class CheckSyncStatus extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonResponseFactory;

    /**
     * CheckSyncStatus constructor.
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
     * Returns status of the latest synchronization task.
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

        if ($queueItem->getStatus() !== QueueItem::COMPLETED) {
            return $response->setData($queueItem->getStatus());
        }

        $queueItem = $this->getQueueService()->findLatestByType('ReceiverSyncTask');
        if ($queueItem === null) {
            return $response->setData(QueueItem::COMPLETED);
        }

        return $response->setData($queueItem->getStatus());
    }

    /**
     * @return QueueService
     */
    private function getQueueService(): BaseQueueService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(BaseQueueService::CLASS_NAME);
    }
}
