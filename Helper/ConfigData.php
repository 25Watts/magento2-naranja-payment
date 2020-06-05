<?php

namespace Watts25\Naranja\Helper;

class ConfigData
extends \Magento\Payment\Helper\Data
{
    const PATH_LOG = 'payment/naranja_webcheckout/logs';
    const CLIENT_ID = 'payment/naranja_webcheckout/client_id';
    const CLIENT_SECRET = 'payment/naranja_webcheckout/client_secret';
    const ENVIRONMENT = 'payment/naranja_webcheckout/environment';
}
