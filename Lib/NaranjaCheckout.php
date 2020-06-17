<?php

namespace Watts25\Naranja\Lib;

use Exception;

class NaranjaCheckout
{
    public function __construct(
        $client_id,
        $client_secret,
        $enviroment,
        $custom_host = false
    ) {
        global $c_id, $c_secret;
        $this->c_id = $client_id;
        $this->c_secret = $client_secret;
        $this->setEnviroment($enviroment, $custom_host);
    }

    private function setEnviroment($enviroment, $custom_host)
    {
        if ($custom_host) {
            // Define custom hosts
            $hosts = $custom_host;
            $basePath = ['/api', '/security-ms'];
            \Naranja\CheckoutApi\Configuration::getDefaultConfiguration()->setHost($hosts[0] . $basePath[0]);
            \Naranja\Security\Configuration::getDefaultConfiguration()->setHost($hosts[1] . $basePath[1]);
            
            return;
        }
        switch ($enviroment) {
            case "prod":
                // Define hosts for prod
                $hosts = array(
                    'https://checkout.apinaranja.com/api',
                    'https://services.apinaranja.com/security-ms'
                );
                \Naranja\CheckoutApi\Configuration::getDefaultConfiguration()->setHost($hosts[0]);
                \Naranja\Security\Configuration::getDefaultConfiguration()->setHost($hosts[1]);
                break;

            case "sandbox":
                $hosts = array(
                    'https://sandbox-checkout.apinaranja.com/api',
                    'https://homoservices.apinaranja.com/security-ms'
                );
                \Naranja\CheckoutApi\Configuration::getDefaultConfiguration()->setHost($hosts[0]);
                \Naranja\Security\Configuration::getDefaultConfiguration()->setHost($hosts[1]);
                break;

            case "develop":
                $hosts = array(
                    'https://e1-checkout.apinaranja.com/api',
                    'https://homoservices.apinaranja.com/security-ms'
                );
                \Naranja\CheckoutApi\Configuration::getDefaultConfiguration()->setHost($hosts[0]);
                \Naranja\Security\Configuration::getDefaultConfiguration()->setHost($hosts[1]);
                break;

            default:
                print_r('invalid environment' . PHP_EOL);
                die();
        }
    }

    private function getToken()
    {
        $tenant_group_id = "clients";
        $array = [
            "client_id" =>  $this->c_id,
            "client_secret" =>  $this->c_secret,
            "audience" =>  "https://naranja.com/ranty/merchants/api",
            "cache" => true
        ];
        $apiInstance = new \Naranja\Security\Api\DefaultApi(new \GuzzleHttp\Client());
        $authenticationb2bpost = new \Naranja\Security\Model\Authenticationb2bpost($array);
        try {
            $result = $apiInstance->postAuth0B2b($tenant_group_id, $authenticationb2bpost);
            return $result;
        } catch (Exception $e) {
            echo 'Exception when calling postAuth0B2b: ', $e->getMessage(), PHP_EOL;
        }
    }

    private function getInstanceApiCheckout($token)
    {
        $value = json_decode($token);
        $config = \Naranja\CheckoutApi\Configuration::getDefaultConfiguration()->setAccessToken($value->access_token);
        $apiInstance = new \Naranja\CheckoutApi\Api\RantyApi(new \GuzzleHttp\Client(), $config);
        return $apiInstance;
    }

    function createPaymentRequest($paymentRequests)
    {
        if (empty($paymentRequests->listInvalidProperties())) {
            $token = $this->getToken();
            $apiCheckoutSdk = $this->getInstanceApiCheckout($token);
            try {
                $result = $apiCheckoutSdk->createPaymentRequest($paymentRequests);
                return $result;
            } catch (Exception $e) {
                echo 'Exception when calling NaranjaCheckout->createPaymentRequest: ', $e->getMessage(), PHP_EOL;
            }
        } else {
            print_r($paymentRequests->listInvalidProperties());
            die();
        }
    }

    function getPaymentRequest($id)
    {
        $token = $this->getToken();
        $apiCheckoutSdk = $this->getInstanceApiCheckout($token);
        try {
            $result = $apiCheckoutSdk->getPaymentRequests($id);
            return $result;
        } catch (Exception $e) {
            echo 'Exception when calling NaranjaCheckout->getPaymentRequests: ', $e->getMessage(), PHP_EOL;
        }
    }

    function getPayment($id)
    {
        $token = $this->getToken();
        $apiCheckoutSdk = $this->getInstanceApiCheckout($token);
        try {
            $result = $apiCheckoutSdk->getPayment($id);
            return $result;
        } catch (Exception $e) {
            echo 'Exception when calling OwnerDxApi->getPayment: ', $e->getMessage(), PHP_EOL;
        }
    }
}
