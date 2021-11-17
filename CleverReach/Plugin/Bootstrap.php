<?php

namespace CleverReach\Plugin;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\AuthProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\OauthStatusProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\UserProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Dashboard\Contracts\DashboardService as DashboardServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Dashboard\DashboardService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Field\Contracts\FieldService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Field\Http\Proxy as FieldProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\Entities\Form;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\FormCacheService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\FormEventsService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\FormEventsService as FormEventsServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Form\Http\Proxy as FormProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Group\Http\Proxy as GroupProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Mailing\Contracts\DefaultMailingService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Mailing\Http\Proxy as MailingProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService as SyncConfigServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\Http\Proxy as ReceiverProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService as ReceiverEventsServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Receiver\SyncConfigService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Segment\Http\Proxy as SegmentProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\WebHookEvent\Http\Proxy as WebHookProxy;
use CleverReach\Plugin\IntegrationCore\Infrastructure\BootstrapComponent;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Configuration\ConfigEntity;
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
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\FormService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\GroupService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\MailingService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\ReceiverEventsService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SegmentService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\SubscriberService;
use CleverReach\Plugin\Services\BusinessLogic\Synchronization\TranslationService;
use CleverReach\Plugin\Services\Infrastructure\LoggerService;

class Bootstrap extends BootstrapComponent
{
    /**
     * Bootstrap constructor.
     */
    public function __construct()
    {
    }

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

    protected static function initInstanceServices(): void
    {
        parent::initServices();

        ServiceRegister::registerService(
            IntegrationCore\Infrastructure\Configuration\Configuration::CLASS_NAME, function () {
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
            IntegrationCore\BusinessLogic\Group\Contracts\GroupService::CLASS_NAME,
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
            IntegrationCore\BusinessLogic\Form\Contracts\FormService::CLASS_NAME,
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
            IntegrationCore\BusinessLogic\Form\Contracts\FormCacheService::CLASS_NAME,
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
            ReceiverEventsServiceContract::CLASS_NAME,
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
            FormEventsServiceContract::CLASS_NAME,
            function () {
                return new Services\BusinessLogic\Synchronization\FormEventsService();
            }
        );

        ServiceRegister::registerService(
            IntegrationCore\BusinessLogic\DynamicContent\Contracts\DynamicContentService::CLASS_NAME,
            function () {
                return new Services\BusinessLogic\Synchronization\DynamicContentService();
            }
        );

        ServiceRegister::registerService(
            FieldProxy::CLASS_NAME,
            function () {
                return new FieldProxy(new CurlHttpClient(), new AuthorizationService());
            }
        );

        ServiceRegister::registerService(
            FieldService::CLASS_NAME,
            function () {
                return new Services\BusinessLogic\Synchronization\FieldService();
            }
        );

        ServiceRegister::registerService(
            IntegrationCore\BusinessLogic\Language\Contracts\TranslationService::CLASS_NAME,
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
            IntegrationCore\BusinessLogic\Segment\Contracts\SegmentService::CLASS_NAME,
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
    }
}
