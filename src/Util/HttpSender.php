<?php
namespace Zm\Ssq\Util;

class HttpSender
{
    const DEFAULT_CONNECT_TIMEOUT = 5;  //默认连接超时
    const DEFAULT_EXECUTE_TIMEOUT = 60; //默认请求超时

    public static function sendRequest($url, $method = "GET", $postData = null, array $headers = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::DEFAULT_EXECUTE_TIMEOUT);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // response headers
        $responseHeaders = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$responseHeaders)
            {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $name = strtolower(trim($header[0]));
                if (!array_key_exists($name, $responseHeaders))
                    $responseHeaders[$name] = [trim($header[1])];
                else
                    $responseHeaders[$name][] = trim($header[1]);

                return $len;
            }
        );

        // set https
        if (0 == strcasecmp('https://', substr($url, 0, 8)))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        // set headers
        if (!empty($headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $request = new \stdClass;
        $request->url = $url;
        $request->method = $method;

        // set post
        if ($method == 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (!empty($postData))
            {
                $request->requestBody = $postData;
                $postData = json_encode($postData);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
        }



        // request
        $response = curl_exec($ch);

        $isFileDownload = -1;
        try {
            $isFileDownload = strcasecmp($responseHeaders["bestsign-file-download"][0], "success");
        } catch (\Throwable $th) {}

        if ($isFileDownload == 0) {
            $contentType = $responseHeaders["content-type"][0];
            $content = base64_encode($response);
            $response = [
                "contentType" => $contentType,
                "content" => $content
            ];
        }
        else
        {
            $response = json_decode($response);
        }

        return $response;
    }
}
