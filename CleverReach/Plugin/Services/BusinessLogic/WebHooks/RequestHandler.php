<?php

namespace CleverReach\Plugin\Services\BusinessLogic\WebHooks;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\WebHooks\Handler;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\WebHookEvent\EventsService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\WebHooks\Contracts\RequestHandlerContract;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;

class RequestHandler implements RequestHandlerContract
{
    /**
     * Prepare response for verifying ReceiverWebHook.
     *
     * @param EventsService $service
     * @param RequestInterface $request
     * @param ResultInterface $result
     *
     * @return ResultInterface
     */
    public function prepareResponse(EventsService $service, RequestInterface $request, ResultInterface $result): ResultInterface
    {
        $secret = $request->getParam('secret');
        $token = $service->getVerificationToken() . ' ' . $secret;
        $result->setHeader('Content-Type', 'text/plain')
            ->setContents($token)
            ->setHttpResponseCode(200);

        return $result;
    }

    /**
     * Initiate WebHook DTO and call handler.
     *
     * @param array $body
     * @param ResultInterface $result
     *
     * @return ResultInterface
     */
    public function callWebhookHandler(array $body, ResultInterface $result): ResultInterface
    {
        $hook = new WebHook($body['condition'], $body['event'], $body['payload']);
        try {
            $this->getReceiverWebhookHandler()->handle($hook);
            $result->setHttpResponseCode(200);
        } catch (UnableToHandleWebHookException $e) {
            $result->setHttpResponseCode($e->getCode())->setContents('Error handling webhook.');
        }

        return $result;
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