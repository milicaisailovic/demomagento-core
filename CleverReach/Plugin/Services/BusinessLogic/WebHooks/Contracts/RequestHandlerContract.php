<?php

namespace CleverReach\Plugin\Services\BusinessLogic\WebHooks\Contracts;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\WebHookEvent\EventsService;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;

interface RequestHandlerContract
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
    public function prepareResponse(EventsService $service, RequestInterface $request, ResultInterface $result): ResultInterface;

    /**
     * Initiate WebHook DTO and call handler.
     *
     * @param array $body
     * @param ResultInterface $result
     *
     * @return ResultInterface
     */
    public function callWebhookHandler(array $body, ResultInterface $result): ResultInterface;
}