<?php

namespace Rybel\backbone;

class LocalLogHelper extends LogHelper
{
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function logMessage($message, $severity = LogVal::info)
    {
        if ($severity != LogVal::error) {
            return;
        }

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

        error_log(json_encode($data));
    }
}
