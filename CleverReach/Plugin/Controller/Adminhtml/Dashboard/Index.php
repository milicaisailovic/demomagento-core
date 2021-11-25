<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Dashboard;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueService as BaseQueueService;
use CleverReach\Plugin\Services\BusinessLogic\Config\CleverReachConfig;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Return CleverReach dashboard page.
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $queueItem = $this->getQueueService()->findLatestByType('InitialSyncTask');
        if ($queueItem !== null && $queueItem->getStatus() === QueueItem::FAILED) {
            try {
                $this->getQueueService()->enqueue('syncQueue', new InitialSyncTask());
            } catch (QueueStorageUnavailableException $e) {
                Logger::logError('Dashboard\Index controller. ' . $e->getMessage());
            }
        }

        $resultPage->setActiveMenu(CleverReachConfig::MENU_ID);

        return $resultPage;
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
