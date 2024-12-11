<?php
namespace Zm\Ssq\Util;

require("HttpSender.php");

class BestSignHttpClient
{
    private $_serverHost;
    private $_clientId;
    private $_clientSecret;
    private $_privateKey;

    private function __construct($serverHost, $clientId, $clientSecret, $privateKey)
    {
        $this->_serverHost = $serverHost;
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_privateKey = openssl_pkey_get_private($privateKey);
    }

    private static $_instance = NULL;
    public static function getInstance($serverHost, $clientId, $clientSecret, $privateKey)
    {
        if (empty($_instance))
        {
            $_instance = new BestSignHttpClient($serverHost, $clientId, $clientSecret, $privateKey);
            $_instance->_getToken();
        }
        return $_instance;
    }

    public function request($uri, $method, $postData, $urlParams = NULL)
    {
        $response = $this->_handleRequest($uri, $method, $postData, $urlParams);
        $response = $this->_handleResponse($response);
        return $response;
    }

    private function _handleRequest($uri, $method, $postData, $urlParams)
    {
        $requestUri = $this->_getUri($uri, $urlParams);
        $requestUrl = $this->_serverHost.$requestUri;

        $accessToken = $this->_getToken();
        $timestamp = time();
        $signature = $this->_getSignature($requestUri, $postData, $timestamp);

        $headers = array();
        array_push($headers, "Content-type: application/json");
        array_push($headers, "bestsign-client-id: {$this->_clientId}");
        array_push($headers, "bestsign-sign-timestamp: {$timestamp}");
        array_push($headers, "bestsign-signature-type: RSA256");
        array_push($headers, "bestsign-signature: {$signature}");
        array_push($headers, "Authorization: bearer {$accessToken}");

        $response = HttpSender::sendRequest($requestUrl, $method, $postData, $headers);
        return $response;
    }

    private function _handleResponse($response)
    {
        $code = $response->code;
        if (is_null($code))
        {
            return $response;
        }
        else {
            if ($code == "0")
            {
                $data = $response->data;
                return $data;
            }
            else 
            {
                return $response;
            }
        }
    }


    private function _getUri($uri, $urlParams)
    {
        if (!empty($urlParams))
        {
            $index = 0;
            foreach($urlParams as $paramName => $paramValue)
            {
                if (!empty($paramValue))
                {
                    if ($index == 0)
                    {
                        $uri .= "?";
                    }
                    else {
                        $uri .= "&";
                    }
                    $uri .= "{$paramName}={$paramValue}";
                    $index++;
                }
            }
        }
        return $uri;
    }


    private $_accessToken;
    private function _getToken()
    {
        if (empty($this->_accessToken))
        {
            $path = "/api/oa2/client-credentials/token";
            $method = "POST";
            $headers = array("Content-type: application/json");

            $url = $this->_serverHost.$path;
            $postData['clientId'] = $this->_clientId;
            $postData['clientSecret'] = $this->_clientSecret;

            $response = HttpSender::sendRequest($url, $method, $postData, $headers);
            $response = $this->_handleResponse($response);

            $this->_accessToken = $response->accessToken ?? '';
        }
        return $this->_accessToken;
    }

    private function _getSignature($uri, $postData, $timestamp)
    {
        $signature = "";

        $requestBodyMD5 = "";
        if (!empty($postData))
        {
            $requestBodyMD5 = json_encode($postData);
            $requestBodyMD5 = md5($requestBodyMD5);
        }
        else {
            $requestBodyMD5 = md5("");
        }

        $signatureString = "bestsign-client-id={$this->_clientId}".
            "bestsign-sign-timestamp={$timestamp}".
            "bestsign-signature-type=RSA256".
            "request-body={$requestBodyMD5}".
            "uri={$uri}";
        $encryptResult = openssl_sign($signatureString, $signature, $this->_privateKey, OPENSSL_ALGO_SHA256);
        if ($encryptResult == false)
        {
            throw new \Exception("Generate Signature Error!");
        }

        $signature = base64_encode($signature);
        $signature = urlencode($signature);
        return $signature;
    }
}
