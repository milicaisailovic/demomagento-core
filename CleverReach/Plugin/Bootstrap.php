<?php

namespace CleverReach\Plugin;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\Plugin\IntegrationCore\Infrastructure\BootstrapComponent;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Configuration\ConfigEntity;
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
use CleverReach\Plugin\Services\BusinessLogic\ConfigurationService;
use CleverReach\Plugin\Services\Infrastructure\LoggerService;

class Bootstrap extends BootstrapComponent
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * @var ConfigurationService
     */
    private static $configurationService;

    /**
     * @var ShopLoggerAdapter
     */
    private static $loggerService;

    /**
     * Bootstrap constructor.
     *
     * @param ConfigurationService $configurationService
     * @param LoggerService $loggerService
     */
    public function __construct(
        ConfigurationService $configurationService,
        LoggerService        $loggerService
    )
    {
        static::$configurationService = $configurationService;
        static::$loggerService = $loggerService;
        static::$instance = $this;
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
        $instance = static::$instance;

        ServiceRegister::registerService(
            ConfigurationService::CLASS_NAME, function () {
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

    }
}
