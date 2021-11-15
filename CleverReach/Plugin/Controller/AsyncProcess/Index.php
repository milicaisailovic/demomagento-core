<?php

namespace CleverReach\Plugin\Controller\AsyncProcess;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\AsyncProcessStarterService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Webapi\Exception;

class Index extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * AsyncProcess constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context     $context,
        JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);

        Bootstrap::init();

        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Run process asynchronously.
     */
    public function execute()
    {
        $guid = $this->getRequest()->getParam('guid');
        if (!$guid) {
            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
            $result->setData(
                [
                    'success' => false,
                    'message' => _('guid is missing'),
                ]
            );

            return $result;
        }

        $this->getAsyncProcessService()->runProcess($guid);

        return $this->resultJsonFactory->create()->setData(['success' => true]);
    }

    /**
     * @return AsyncProcessStarterService
     */
    private function getAsyncProcessService(): AsyncProcessStarterService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(AsyncProcessService::CLASS_NAME);
    }
}
