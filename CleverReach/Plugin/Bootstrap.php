<?php

namespace CleverReach\Plugin;


use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\AuthProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\OauthStatusProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\UserProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Dashboard\Contracts\DashboardService as DashboardServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Dashboard\DashboardService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\DynamicContent\Contracts\DynamicContentService as DynamicContentServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Field\Contracts\FieldService as FieldServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Field\Http\Proxy as FieldProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\Contracts\FormCacheService as FormCacheServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\Contracts\FormService as FormServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\Entities\Form;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\FormCacheService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\FormEventsService as BaseFormEventsService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\Http\Proxy as FormProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Group\Contracts\GroupService as GroupServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Group\Http\Proxy as GroupProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Language\Contracts\TranslationService as TranslationServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Mailing\Contracts\DefaultMailingService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Mailing\Http\Proxy as MailingProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService as SyncConfigServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverCreatedEvent;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverEventBus;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverSubscribedEvent;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverUnsubscribedEvent;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverUpdatedEvent;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Http\Proxy as ReceiverProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService as BaseReceiverEventsService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\SyncConfigService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\WebHooks\Handler as ReceiverHandler;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Segment\Contracts\SegmentService as SegmentServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Segment\Http\Proxy as SegmentProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\WebHookEvent\Http\Proxy as WebHookProxy;
use CleverReach\Plugin\IntegrationCore\Infrastructure\BootstrapComponent;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Configuration\ConfigEntity;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Configuration\Configuration as BaseConfiguration;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Http\CurlHttpClient;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Serializer\Concrete\JsonSerializer;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Process;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\Plugin\Repository\BaseRepository;
use CleverReach\Plugin\Repository\QueueItemRepository;
use CleverReach\Plugin\Services\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\Plugin\Services\BusinessLogic\ConfigurationService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\CustomerService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\DynamicContentService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\FieldService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\FormEventsService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\FormService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\GroupService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\MailingService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\ReceiverEventsService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SegmentService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SubscriberService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\TranslationService;
use CleverReach\Plugin\Services\BusinessLogic\WebHooks\Contracts\RequestHandlerContract;
use CleverReach\Plugin\Services\BusinessLogic\WebHooks\ReceiverWebhookHandler;
use CleverReach\Plugin\Services\BusinessLogic\WebHooks\RequestHandler;
use CleverReach\Plugin\Services\Infrastructure\LoggerService;

class Bootstrap extends BootstrapComponent
{
    /**
     * Initializes infrastructure services and utilities.
     */
    protected static function initServices(): void
    {
        parent::initServices();

        static::initInstanceServices();
    }

    /**
     * Initializes repositories.
     *
     * @throws RepositoryClassException
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();

        RepositoryRegistry::registerRepository(Process::class, BaseRepository::getClassName());
        RepositoryRegistry::registerRepository(ConfigEntity::class, BaseRepository::getClassName());
        RepositoryRegistry::registerRepository(QueueItem::class, QueueItemRepository::getClassName());
        RepositoryRegistry::registerRepository(Form::class, BaseRepository::getClassName());
    }

    /**
     * Initialize services.
     */
    protected static function initInstanceServices(): void
    {
        parent::initServices();

        ServiceRegister::registerService(
            BaseConfiguration::CLASS_NAME, function () {
            return new ConfigurationService();
        });

        ServiceRegister::registerService(
            ShopLoggerAdapter::CLASS_NAME,
            function () {
                return new LoggerService();
            }
        );

        ServiceRegister::registerService(
            QueueService::CLASS_NAME,
            function () {
                return new QueueService();
            }
        );

        ServiceRegister::registerService(
            Serializer::CLASS_NAME,
            function () {
                return new JsonSerializer();
            }
        );

        ServiceRegister::registerService(
            DefaultLoggerAdapter::CLASS_NAME,
            function () {
                return new LoggerService();
            }
        );

        ServiceRegister::registerService(
            AuthorizationServiceContract::CLASS_NAME,
            function () {
                return new AuthorizationService();
            }
        );

        ServiceRegister::registerService(
            AuthProxy::CLASS_NAME,
            function () {
                return new AuthProxy(new CurlHttpClient());
            }
        );

        ServiceRegister::registerService(
            OauthStatusProxy::CLASS_NAME,
            function () {
                return new OauthStatusProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            UserProxy::CLASS_NAME,
            function () {
                return new UserProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            GroupServiceContract::CLASS_NAME,
            function () {
                return new GroupService();
            }
        );

        ServiceRegister::registerService(
            GroupProxy::CLASS_NAME,
            function () {
                return new GroupProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            FormServiceContract::CLASS_NAME,
            function () {
                return new FormService();
            }
        );

        ServiceRegister::registerService(
            FormProxy::CLASS_NAME,
            function () {
                return new FormProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            FormCacheServiceContract::CLASS_NAME,
            function () {
                return new FormCacheService();
            }
        );

        ServiceRegister::registerService(
            MailingProxy::CLASS_NAME,
            function () {
                return new MailingProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            DefaultMailingService::CLASS_NAME,
            function () {
                return new MailingService();
            }
        );

        ServiceRegister::registerService(
            BaseReceiverEventsService::CLASS_NAME,
            function () {
                return new ReceiverEventsService();
            }
        );

        ServiceRegister::registerService(
            WebHookProxy::CLASS_NAME,
            function () {
                return new WebHookProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            BaseFormEventsService::CLASS_NAME,
            function () {
                return new FormEventsService();
            }
        );

        ServiceRegister::registerService(
            DynamicContentServiceContract::CLASS_NAME,
            function () {
                return new DynamicContentService();
            }
        );

        ServiceRegister::registerService(
            FieldProxy::CLASS_NAME,
            function () {
                return new FieldProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            FieldServiceContract::CLASS_NAME,
            function () {
                return new FieldService();
            }
        );

        ServiceRegister::registerService(
            TranslationServiceContract::CLASS_NAME,
            function () {
                return TranslationService::getInstance();
            }
        );

        ServiceRegister::registerService(
            SegmentProxy::CLASS_NAME,
            function () {
                return new SegmentProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            SegmentServiceContract::CLASS_NAME,
            function () {
                return new SegmentService();
            }
        );

        ServiceRegister::registerService(
            ReceiverProxy::CLASS_NAME,
            function () {
                return new ReceiverProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            SyncConfigServiceContract::CLASS_NAME,
            function () {
                return SyncConfigService::getInstance();
            }
        );

        ServiceRegister::registerService(
            DashboardServiceContract::CLASS_NAME,
            function () {
                return new DashboardService();
            }
        );

        ServiceRegister::registerService(
            CustomerService::class,
            function () {
                return new CustomerService();
            }
        );

        ServiceRegister::registerService(
            SubscriberService::class,
            function () {
                return new SubscriberService();
            }
        );

        ServiceRegister::registerService(
            ReceiverHandler::class,
            function () {
                return new ReceiverHandler();
            }
        );

        ServiceRegister::registerService(
            ReceiverEventBus::class,
            function () {
                return ReceiverEventBus::getInstance();
            }
        );

        ServiceRegister::registerService(
            RequestHandlerContract::class,
            function () {
                return new RequestHandler();
            }
        );
    }

    /**
     * Initialize events.
     */
    public static function initEvents()
    {
        parent::initEvents();

        $receiverEventBus = ServiceRegister::getService(ReceiverEventBus::CLASS_NAME);

        $receiverEventBus->when(
            ReceiverUpdatedEvent::CLASS_NAME,
            function (ReceiverUpdatedEvent $event) {
                $handler = new ReceiverWebhookHandler();
                $handler->receiverUpdated($event->getReceiverId());
            }
        );

        $receiverEventBus->when(
            ReceiverCreatedEvent::CLASS_NAME,
            function (ReceiverCreatedEvent $event) {
                $handler = new ReceiverWebhookHandler();
                $handler->receiverCreated($event->getReceiverId());
            }
        );

        $receiverEventBus->when(
            ReceiverSubscribedEvent::CLASS_NAME,
            function (ReceiverSubscribedEvent $event) {
                $handler = new ReceiverWebhookHandler();
                $handler->receiverSubscribed($event->getReceiverId());
            }
        );

        $receiverEventBus->when(
            ReceiverUnsubscribedEvent::CLASS_NAME,
            function (ReceiverUnsubscribedEvent $event) {
                $handler = new ReceiverWebhookHandler();
                $handler->receiverUnsubscribed($event->getReceiverId());
            }
        );
    }
}
