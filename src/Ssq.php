<?php

namespace Zm\Ssq;

use Zm\Ssq\Util\BestSignHttpClient;

class Ssq
{

    protected $client;


    // 上上签服务器地址
    //     预发布环境：https://api.bestsign.info
    //     正式环境：https://api.bestsign.cn
    // 注意：这里后面不要加'/'
    private $serverHost = "https://api.bestsign.cn";
    // 公司名称
    protected $companyName;
    protected $contractType;

    protected $pushUrl;
    protected $httpClient;

    protected $sealName;
    // 上上签管理员账号
    protected $account;

    public function __construct()
    {
        $clientId = config('ssq.clientId');
        // 应用秘钥
        $clientSecret = config('ssq.clientSecret');
        // RSA签名私钥
        // 注意：在上上签开放平台上生成的密钥对，其私钥格式为PKCS#8
        $privateKey = config('ssq.privateKey');

        $this->companyName = config('ssq.companyName');

        $this->contractType = config('ssq.contractType');

        $this->pushUrl = config('ssq.pushUrl');

        $this->sealName = config('ssq.sealName');

        $this->account = config('ssq.account');

        $this->httpClient = BestSignHttpClient::getInstance($this->serverHost, $clientId, $clientSecret, $privateKey);
    }



    public function bindingExistence($devAccountId)
    {
        $path = '/api/users/binding-existence';
        $method = "POST";

        $postData['devAccountId'] = $devAccountId;

        $response = $this->httpClient->request($path, $method, $postData);
        return $response;
    }

    public function bindAccount($devAccountId, $account, $contractId)
    {
        $path = '/api/users/binding-url';
        $method = "POST";
        $postData = [
            "ssqAccount" => $account,
            "devAccountId" => $devAccountId,
            "userType" => "1",
            "targetPage" =>  "signing",
            'contractId' => $contractId,
            'returnUrl' => '/tabBar/index/index'
        ];
        $response = $this->httpClient->request($path, $method, $postData);
        return $response;
    }


//--------------------- Util ---------------------//


    public function downloadContractAppendix($contractId, $account, $enterpriseName)
    {
        $path = "/api/contracts/{$contractId}/appendix-file";
        $method = "GET";

        $urlParams['account'] = $account;
        //中文需要encode
        $urlParams['enterpriseName'] = urlencode($enterpriseName);

        $response = $this->httpClient->request($path, $method, null, $urlParams);
        return $response;
    }

    public function queryBindingStatus($devAccountId)
    {
        $path = "/api/users/binding-existence";
        $method = "POST";

        $postData['devAccountId'] = $devAccountId;

        $response = $this->httpClient->request($path, $method, $postData);
        return $response;
    }

    /**
     * 发送并且创建合同
     * @param $receivers
     */
    public function sendContract($placeHolders, $account = '', $templateId = '', $roleId = '', $userAccount = '', $userName = '')
    {
        $postData = [
            'placeHolders' => $placeHolders,
            'templateId' => $templateId,
            'userInfo' => [
                'userName' => $userName,
                'userAccount' => $userAccount,
            ],
            'roles' => [
                [
                    'enterpriseName' => $this->companyName,
                    'ifProxyClaimer' => false,
                    'roleId' => $roleId,
                    'userAccount' => $account ?: $this->account,
                ]
            ],
            'sender' => [
                'account' => $account ?: $this->account,
                'enterpriseName' => $this->companyName
            ],
            'contractType' => $this->contractType,
            'pushUrl' => $this->pushUrl
        ];

        $path = "/api/templates/send-contracts-sync";
        $method = "POST";
        $response = $this->httpClient->request($path, $method, $postData);
        return $response;
    }

    public function downloadByOrder($contractId)
    {
        $postData = [
            'contractId' => $contractId,
            'order' => 0
        ];
        $path = "/api/contracts/downloadByOrder";
        $method = "POST";
        $response = $this->httpClient->request($path, $method, $postData);
        return $response;
    }

    public function revoke($contractId, $revokeReason)
    {
        $postData = [
            'revokeReason' => $revokeReason
        ];
        $path = "/api/contracts/".$contractId."/revoke";
        $method = "POST";
        $response = $this->httpClient->request($path, $method, $postData);
        return $response;
    }

    public function detail($contractId)
    {
        $path = "/api/contracts/detail/".$contractId;
        $method = "GET";

        $urlParams['enterpriseName'] = '';		//中文需要encode

        $response = $this->httpClient->request($path, $method, null, $urlParams);
        return $response;
    }

    public function link($contractId, $devAccountId)
    {
        $postData = [
            'contractId'=> $contractId,
            'devAccountId' => $devAccountId,
            'targetPage' => 'signing'
        ];
        $path = "/api/users/sso-link";
        $method = "POST";
        $response = $this->httpClient->request($path, $method, $postData);
        return $response;
    }

    public function sign($contractIds)
    {

        $postData = [
            'contractIds'=> $contractIds,
            'sealName' => $this->sealName,
            'signer' => [
                'account' => $this->account,
                'enterpriseName' => $this->companyName
            ],
            'pushUrl' => $this->pushUrl
        ];
        $path = "/api/contracts/sign";
        $method = "POST";
        $response = $this->httpClient->request($path, $method, $postData);
        return $response;
    }

    public function templateDetail($id)
    {
        $path = "/api/templates/{$id}";
        $method = "GET";
        $response = $this->httpClient->request($path, $method, null, '');
        return $response;
    }

}
