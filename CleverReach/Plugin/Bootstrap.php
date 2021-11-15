<?php

namespace CleverReach\Plugin;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceContract;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\AuthProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\OauthStatusProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Http\UserProxy;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
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
    }
}
