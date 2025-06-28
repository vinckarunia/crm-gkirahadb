<?php

namespace ChurchCRM\Utils;

use ChurchCRM\dto\SystemConfig;
use ChurchCRM\dto\SystemURLs;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

class LoggerUtils
{
    private static ?Logger $appLogger = null;
    private static ?StreamHandler $appLogHandler = null;
    private static ?Logger $cspLogger = null;
    private static ?Logger $authLogger = null;
    private static ?Logger $slimLogger = null;
    private static ?StreamHandler $authLogHandler = null;
    private static ?string $correlationId = null;

    public static function getCorrelationId(): ?string
    {
        if (empty(self::$correlationId)) {
            self::$correlationId = uniqid();
        }

        return self::$correlationId;
    }

    public static function getLogLevel(): int
    {
        return intval(SystemConfig::getValue('sLogLevel'));
    }

    public static function isDebugLogLevel(): bool
    {
        return SystemConfig::getValue('sLogLevel') == Logger::DEBUG;
    }

    public static function buildLogFilePath(string $type): string
    {
        return SystemURLs::getDocumentRoot() . '/logs/' . date('Y-m-d') . '-' . $type . '.log';
    }

    public static function getSlimMVCLogger(): Logger
    {
        if (!self::$slimLogger instanceof Logger) {
            $slimLogger = new Logger('slim-app');
            $streamHandler = new StreamHandler(self::buildLogFilePath('slim'), SystemConfig::getValue('sLogLevel'));
            $slimLogger->pushHandler($streamHandler);
            self::$slimLogger = $slimLogger;
        }

        return self::$slimLogger;
    }

    /**
     * @return Logger
     */
    public static function getAppLogger($level = null): ?Logger
    {
        if (!self::$appLogger instanceof Logger) {
            if ($level === null) {
                $level = self::getLogLevel();
            }

            self::$appLogger = new Logger('defaultLogger');
            self::$appLogHandler = new StreamHandler(self::buildLogFilePath('app'), $level);
            self::$appLogger->pushHandler(self::$appLogHandler);
            self::$appLogger->pushProcessor(new PsrLogMessageProcessor());
            self::$appLogger->pushProcessor(function (array $entry): array {
                $entry['extra']['url'] = $_SERVER['REQUEST_URI'];
                $entry['extra']['remote_ip'] = $_SERVER['REMOTE_ADDR'];
                $entry['extra']['correlation_id'] = self::getCorrelationId();

                return $entry;
            });
        }

        return self::$appLogger;
    }

    private static function getCaller(): array
    {
        $callers = debug_backtrace();
        $call = [];
        if ($callers[5]) {
            $call = $callers[5];
        }

        return [
            'ContextClass'  => array_key_exists('class', $call) ? $call['class'] : '',
            'ContextMethod' => $call['function'],
        ];
    }

    /**
     * @return Logger
     */
    public static function getAuthLogger(): ?Logger
    {
        if (!self::$authLogger instanceof Logger) {
            self::$authLogger = new Logger('authLogger');
            self::$authLogHandler = new StreamHandler(self::buildLogFilePath('auth'), SystemConfig::getValue('sLogLevel'));
            self::$authLogger->pushHandler(self::$authLogHandler);
            self::$authLogger->pushProcessor(function (array $entry): array {
                $entry['extra']['url'] = $_SERVER['REQUEST_URI'];
                $entry['extra']['remote_ip'] = $_SERVER['REMOTE_ADDR'];
                $entry['extra']['correlation_id'] = self::getCorrelationId();
                $entry['extra']['context'] = self::getCaller();

                return $entry;
            });
        }

        return self::$authLogger;
    }

    public static function resetAppLoggerLevel(): void
    {
        // If the app log handler was initialized (in the bootstrapper) to a specific level
        // before the database initialization occurred,
        // we provide a function to reset the app logger to what's defined in the database.
        self::$appLogHandler->setLevel(self::getLogLevel());
    }

    public static function getCSPLogger(): ?Logger
    {
        if (!self::$cspLogger instanceof Logger) {
            self::$cspLogger = new Logger('cspLogger');
            self::$cspLogger->pushHandler(new StreamHandler(self::buildLogFilePath('csp'), self::getLogLevel()));
        }

        return self::$cspLogger;
    }
}
