<?php

namespace Oh86\LaravelSmBeijingCa\Apis;

use GuzzleHttp\Client;
use Oh86\SmCryptor\Exceptions\HttpRequestException;
use Ramsey\Uuid\Uuid;

class BeijingCaApi
{
    /**
     * @var array{
     *           baseUrl: string,
     *           appId: string,
     *           deviceId: string,
     *           hmacKey: string,
     *      }
     */
    private $config;

    public const Version = '1.0';

    /**
     * @param array{
     *              baseUrl: string,
     *              appId: string,
     *              deviceId: string,
     *              hmacKey: string,
     *        } $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }


    /**
     * 附加公共参数（包括签名参数）
     * @param array $params
     */
    protected function appendCommonParams(array &$params)
    {
        $params['version'] = self::Version;
        $params['signAlgo'] = 'HmacSHA256';
        $params['deviceId'] = $this->config['deviceId'];
        $params['appId'] = $this->config['appId'];
        $params['signature'] = $this->genSignature($params);
    }

    /**
     * 生成签名
     * @param array<string,string> $params
     * @return string
     */
    public function genSignature(array $params): string
    {
        ksort($params);
        $arr = [];
        foreach ($params as $k => $v) {
            if ($k == 'signature') { // 忽略signature参数
                continue;
            }

            $arr[] = sprintf('%s=%s', $k, $v);
        }
        $str1 = implode('&', $arr);
        return base64_encode(hash_hmac('sha256', $str1, $this->config['hmacKey'], true));
    }

    /**
     * 生成主密钥
     * @param string $keySpec
     * @param array $options
     * @return array{keyId:string, cmkMetadata:array}
     * @throws HttpRequestException
     */
    public function createKey($keySpec = 'SM4_128', $options = [])
    {

        $url = $this->config['baseUrl'] . '/chiron/v1/system/createKey';
        $params = [
            'transId' => (string) Uuid::uuid1()->getHex(),
            'keyUsage' => 'ENCRYPT/DECRYPT',
            'keySpec' => $keySpec,
        ] + $options;
        $this->appendCommonParams($params);

        $client = new Client();
        $response = $client->post($url, [
            'json' => $params,
        ]);

        $body = $response->getBody()->getContents();
        $arr = json_decode($body, true);
        if (($arr['status'] ?? null) != 200) {
            throw new HttpRequestException($response->getStatusCode(), $body, $url, $params);
        }

        return $arr['data'];
    }

    /**
     * 生成hmacSm3哈希值
     * @param string $source  源文本，base64编码
     * @param string $keyId
     * @param string|null $keyVersion  
     * @param array $options
     * @return array{
     *              hmac:string,  // base64编码
     *         }
     * @throws HttpRequestException
     */
    public function hmacSm3($source, $keyId, $keyVersion = null, $options = [])
    {
        $url = $this->config['baseUrl'] . '/cmk/v1/hmac';

        $params = [
            'transId' => (string) Uuid::uuid1()->getHex(),
            'keyId' => $keyId,
            'source' => $source,
        ] + $options;
        if ($keyVersion) {
            $params['keyVersion'] = $keyVersion;
        }

        $this->appendCommonParams($params);

        $client = new Client();
        $response = $client->post($url, [
            'json' => $params,
        ]);

        $body = $response->getBody()->getContents();
        $arr = json_decode($body, true);
        if (($arr['status'] ?? null) != 200) {
            throw new HttpRequestException($response->getStatusCode(), $body, $url, $params);
        }

        return $arr['data'];
    }

    /**
     * 加密，默认使用sm4算法
     * @param string $plainText   待加密明文，base64编码
     * @param string $keyId
     * @param string $mode        加密模式，默认为ECB，支持ECB/CBC
     * @param string $padding
     * @param array $options
     * @return array{
     *              cipherTextBlob:string,    // 加密结果，base64编码
     *         }
     * @throws HttpRequestException
     */
    public function encrypt($plainText, $keyId, $mode = 'ECB', $padding = 'PKCS7Padding', $options = [])
    {
        $url = $this->config['baseUrl'] . '/cmk/v1/encrypt';

        $params = [
            'transId' => (string) Uuid::uuid1()->getHex(),
            'keyId' => $keyId,
            'plainText' => $plainText,
            // 'iv' => $iv, // mode为CBC时需要传入iv，通过option参数传入
            'mode' => $mode,
            'padding' => $padding,
        ] + $options;

        $this->appendCommonParams($params);

        $client = new Client();
        $response = $client->post($url, [
            'json' => $params,
        ]);

        $body = $response->getBody()->getContents();
        $arr = json_decode($body, true);
        if (($arr['status'] ?? null) != 200) {
            throw new HttpRequestException($response->getStatusCode(), $body, $url, $params);
        }

        return $arr['data'];
    }

    /**
     * 解密，默认使用sm4算法
     * @param mixed $encData   待解密密文，base64编码
     * @param mixed $keyId
     * @param array $options
     * @return array{
     *            plaintext:string,   // 解密结果，base64编码
     *         }
     * @throws HttpRequestException
     */
    public function decrypt($encData, $keyId, $options = [])
    {
        $url = $this->config['baseUrl'] . '/cmk/v1/decrypt';

        $params = [
            'transId' => (string) Uuid::uuid1()->getHex(),
            'keyId' => $keyId,
            'encData' => $encData,
        ] + $options;

        $this->appendCommonParams($params);

        $client = new Client();
        $response = $client->post($url, [
            'json' => $params,
        ]);

        $body = $response->getBody()->getContents();
        $arr = json_decode($body, true);
        if (($arr['status'] ?? null) != 200) {
            throw new HttpRequestException($response->getStatusCode(), $body, $url, $params);
        }

        return $arr['data'];
    }
}
