<?php

namespace CleverReach\Plugin\Controller\Event;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\FormEventsService as FormEventsServiceAlias;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\FormEventsService;
use CleverReach\Plugin\Services\BusinessLogic\WebHooks\RequestHandler;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class FormWebHook extends Action
{
    /**
     * ReceiverWebHook controller constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * Send information for verifying FormWebHook.
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        
        return $this->getRequestHandler()->prepareResponse($this->getEventsService(), $this->getRequest(), $result);
    }

    /**
     * @return FormEventsService
     */
    private function getEventsService(): FormEventsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(FormEventsServiceAlias::CLASS_NAME);
    }

    /**
     * @return RequestHandler
     */
    private function getRequestHandler(): RequestHandler
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(RequestHandler::class);
    }
}