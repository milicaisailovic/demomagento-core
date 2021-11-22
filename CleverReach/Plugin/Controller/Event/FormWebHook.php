<?php

namespace CleverReach\Plugin\Controller\Event;

use CleverReach\Plugin\Bootstrap;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\FormEventsService as FormEventsServiceAlias;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\FormEventsService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

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
        Bootstrap::init();
    }

    /**
     * Send information for verifying FormWebHook.
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
     * @return FormEventsService
     */
    private function getEventsService(): FormEventsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(FormEventsServiceAlias::CLASS_NAME);
    }
}