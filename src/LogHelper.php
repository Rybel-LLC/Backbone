<?php

namespace Rybel\backbone;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;

class LogHelper
{
    private $logger;
    private $type;

    public function __construct(string $appName, array $awsConfig, string $type)
    {
        $this->type = $type;

        $cwClient  = new CloudWatchLogsClient($awsConfig);
        // Log group name, will be created if none
        $cwGroupName = 'app-logs';
        // Log stream name, will be created if none
        $cwStreamNameInstance = ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? "dev-" : "prod-") . $this->type;
        // Days to keep logs, 14 by default
        $cwRetentionDays = 60;

        $cwHandlerInfo = new CloudWatch($cwClient, $cwGroupName, $cwStreamNameInstance, $cwRetentionDays, 10000, [ 'application' => $appName ], Logger::INFO);
        $cwHandlerError = new CloudWatch($cwClient, $cwGroupName, $cwStreamNameInstance, $cwRetentionDays, 10000, [ 'application' => $appName ], Logger::ERROR);
        $cwHandlerWarning = new CloudWatch($cwClient, $cwGroupName, $cwStreamNameInstance, $cwRetentionDays, 10000, [ 'application' => $appName ], Logger::WARNING);

        $this->logger = new Logger($appName);

        $formatter = new LineFormatter(null, null, false, true);

        $cwHandlerInfo->setFormatter($formatter);
        $cwHandlerError->setFormatter($formatter);
        $cwHandlerWarning->setFormatter($formatter);

        $this->logger->pushHandler($cwHandlerInfo);
        $this->logger->pushHandler($cwHandlerError);
        $this->logger->pushHandler($cwHandlerWarning);
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function logMessage($message, $severity = LogVal::info)
    {
        $e = new \Exception();

        $data = array(
            "message" => $message,
            "stackTrace" => $e->getTrace()
        );

        if ($this->type != LogStream::cron) {
            $data["ip"] = $_SERVER['REMOTE_ADDR'];
        }

        if (!empty($_SESSION['username'])) {
            $data['username'] = $_SESSION['username'];
        }

        if ($severity == LogVal::warning) {
            $this->logger->warning(json_encode($data));
        } elseif ($severity == LogVal::error) {
            $this->logger->error(json_encode($data));
        } else {
            $this->logger->info(json_encode($data));
        }
    }
}
