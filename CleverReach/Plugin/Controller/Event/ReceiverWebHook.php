<?php

namespace CleverReach\Plugin\Controller\Event;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService as ReceiverEventsServiceAlias;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\WebHooks\Handler;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\ReceiverEventsService;
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
            $secret = $this->getRequest()->getParam('secret');
            $token = $this->getEventsService()->getVerificationToken() . ' ' . $secret;

            $result->setHeader('Content-Type', 'text/plain')
                ->setContents($token)
                ->setHttpResponseCode(200);
        }

        if ($method === 'POST') {
            $requestBody = $this->getRequest()->getContent();
            $decodedBody = json_decode($requestBody, true);
            $hook = new WebHook($decodedBody['condition'], $decodedBody['event'], $decodedBody['payload']);
            try {
                $this->getReceiverWebhookHandler()->handle($hook);
                $result->setHttpResponseCode(200);
            } catch (UnableToHandleWebHookException $e) {
                $result->setHttpResponseCode($e->getCode())->setContents('Error handling webhook.');
            }
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
     * @return Handler
     */
    private function getReceiverWebhookHandler(): Handler
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(Handler::class);
    }
}
