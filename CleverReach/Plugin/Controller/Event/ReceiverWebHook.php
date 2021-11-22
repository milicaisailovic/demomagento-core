<?php

namespace CleverReach\Plugin\Controller\Event;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService as ReceiverEventsServiceAlias;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\ReceiverEventsService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class ReceiverWebHook extends Action
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

        Bootstrap::init();
    }

    /**
     * Send information for verifying ReceiverWebHook.
     *
     * @return void
     */
    public function execute()
    {
        $secret = $this->getRequest()->getParam('secret');
        $token = $this->getEventsService()->getVerificationToken() . ' ' . $secret;
        header('Content-Type: text/html');
        echo $token;
    }

    /**
     * @return ReceiverEventsService
     */
    private function getEventsService(): ReceiverEventsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(ReceiverEventsServiceAlias::CLASS_NAME);
    }
}
