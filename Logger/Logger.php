<?php

namespace Watts25\Naranja\Logger;

/**
 * Watts25 custom logger allows name changing to differentiate log call origin
 * Class Logger
 *
 * @package Watts25\Naranja\Logger
 */
class Logger
extends \Monolog\Logger
{

    /**
     * Set logger name
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
