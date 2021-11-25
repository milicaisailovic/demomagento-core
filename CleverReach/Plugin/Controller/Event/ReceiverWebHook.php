<?php

namespace CleverReach\Plugin\Controller\Event;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService as ReceiverEventsServiceAlias;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\ReceiverEventsService;
use CleverReach\Plugin\Services\BusinessLogic\WebHooks\RequestHandler;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class ReceiverWebHook extends Action implements CsrfAwareActionInterface
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
     * If method is 'GET', send information for verifying ReceiverWebHook.
     * If method is 'POST', create WebHook DTO and call Receiver handler.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $method = $this->_request->getMethod();
        if ($method === 'GET') {
            $result = $this->getRequestHandler()->prepareResponse($this->getEventsService(), $this->getRequest(), $result);
        }

        if ($method === 'POST') {
            $requestBody = $this->getRequest()->getContent();
            $decodedBody = json_decode($requestBody, true);
            $result = $this->getRequestHandler()->callWebhookHandler($decodedBody, $result);
        }

        return $result;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @return ReceiverEventsService
     */
    private function getEventsService(): ReceiverEventsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(ReceiverEventsServiceAlias::CLASS_NAME);
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
