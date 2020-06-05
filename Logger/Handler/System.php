<?php

namespace Watts25\Naranja\Logger\Handler;

use Monolog\Logger;

/**
 * Watts25 logger handler
 */
class System
extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/watts25_naranja.log';
}
