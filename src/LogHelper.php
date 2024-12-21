<?php

namespace Rybel\backbone;

abstract class LogHelper
{
    abstract function logMessage($message, $severity = LogVal::info);
}
