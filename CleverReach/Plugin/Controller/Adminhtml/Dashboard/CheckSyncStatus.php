<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Dashboard;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService as BaseQueueService;
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
        $initialSync = $this->getLatestQueueItemByType('InitialSyncTask');
        $receiverSync = $this->getLatestQueueItemByType('ReceiverSyncTask');
        if ($initialSync === null) {
            return $response->setData('error');
        }

        if (!$this->isInitialSyncCompletedAndReceiverSyncExists($initialSync, $receiverSync)) {
            return $response->setData($initialSync->getStatus());
        }

        return $response->setData($receiverSync->getStatus());
    }

    /**
     * @param string $type
     *
     * @return QueueItem|null
     */
    private function getLatestQueueItemByType(string $type): ?QueueItem
    {
        return $this->getQueueService()->findLatestByType($type);
    }

    /**
     * @param QueueItem $initialSync
     * @param QueueItem|null $receiverSync
     *
     * @return bool
     */
    private function isInitialSyncCompletedAndReceiverSyncExists(QueueItem $initialSync, ?QueueItem $receiverSync): bool
    {
        return $initialSync->getStatus() === QueueItem::COMPLETED && $receiverSync !== null;
    }

    /**
     * @return QueueService
     */
    private function getQueueService(): QueueService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(BaseQueueService::CLASS_NAME);
    }
}
