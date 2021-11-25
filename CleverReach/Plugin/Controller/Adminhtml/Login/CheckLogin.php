<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Login;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService as SyncConfigServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\SyncConfigService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueService as BaseQueueService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\CustomerService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SubscriberService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class CheckLogin extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonResponseFactory;

    /**
     * CheckLogin constructor.
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
     * Check if authorization is done.
     *
     * @return Json
     */
    public function execute(): Json
    {
        $response = $this->jsonResponseFactory->create();

        $queueItem = $this->getQueueService()->findLatestByType('ConnectTask');
        if ($queueItem !== null && $queueItem->getStatus() === QueueItem::COMPLETED) {
            $services = [
                new SyncService('service-' . CustomerService::class, 2, CustomerService::class),
                new SyncService('service-' . SubscriberService::class, 1, SubscriberService::class)
            ];

            $this->getSyncConfigService()->setEnabledServices($services);
            $this->_redirect('cleverreach/dashboard/synchronization');

            return $response->setData(['success' => true, 'url' => $this->getUrl('cleverreach/dashboard/index')]);
        }

        return $response->setData(['success' => false]);
    }

    /**
     * @return QueueService
     */
    private
    function getQueueService(): BaseQueueService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(BaseQueueService::CLASS_NAME);
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
