<?php

namespace CleverReach\Plugin\Services\Infrastructure;

use CleverReach\Plugin\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Logger\LogData;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\ConfigurationService;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

class LoggerService implements ShopLoggerAdapter
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
    /**
     * Log level names for corresponding log level codes.
     *
     * @var array
     */
    private static $logLevelName = [
        Logger::ERROR => 'error',
        Logger::WARNING => 'warning',
        Logger::INFO => 'info',
        Logger::DEBUG => 'debug',
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Logger service constructor.
     */
    public function __construct()
    {
        $objectManager = ObjectManager::getInstance();
        $this->logger = $objectManager->create(LoggerInterface::class);

        static::$instance = $this;
    }

    /**
     * Logs message in the system.
     *
     * @param LogData $data
     */
    public function logMessage(LogData $data)
    {
        /** @var ConfigurationService $configService */
        $configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        $minLogLevel = $configService->getMinLogLevel();
        $logLevel = $data->getLogLevel();

        if (($logLevel > $minLogLevel) && !$configService->isDebugModeEnabled()) {
            return;
        }

        $message = 'CLEVERREACH LOG:
            Date: ' . date('d/m/Y') . '
            Time: ' . date('H:i:s') . '
            Log level: ' . self::$logLevelName[$logLevel] . '
            Message: ' . $data->getMessage();
        $context = $data->getContext();
        if (!empty($context)) {
            $message .= '
            Context data: [';
            foreach ($context as $item) {
                $message .= '"' . $item->getName() . '" => "' . print_r($item->getValue(), true) . '", ';
            }

            $message .= ']';
        }

        \call_user_func([$this->logger , self::$logLevelName[$logLevel]], $message);
    }
}
