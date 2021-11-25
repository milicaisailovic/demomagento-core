<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Dashboard;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\ReceiverSyncTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class ManualSynchronization extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonResponseFactory;

    /**
     * ManualSynchronization controller.
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
     * Enqueue ReceiverSyncTask.
     *
     * @return Json
     */
    public function execute(): Json
    {
        $response = $this->jsonResponseFactory->create();
        try {
            $this->getQueueService()->enqueue('syncQueue', new ReceiverSyncTask());
        } catch (BaseException $e) {
            Logger::logError('Dashboard\ManualSynchronization controller. ' . $e->getMessage());

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
