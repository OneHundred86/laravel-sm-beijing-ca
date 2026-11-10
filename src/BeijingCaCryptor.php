<?php

namespace Oh86\LaravelSmBeijingCa;

use Illuminate\Support\Facades\Log;
use Oh86\LaravelSmBeijingCa\Apis\BeijingCaApi;
use Oh86\SmCryptor\AbstractCryptor;
use Oh86\SmCryptor\Exceptions\HttpRequestException;
use Oh86\SmCryptor\Exceptions\SmCryptorException;

class BeijingCaCryptor extends AbstractCryptor
{
    /**
     * @var array{
     *           baseUrl: string,
     *           appId: string,
     *           deviceId: string,
     *           hmacKey: string,
     *      }
     */
    protected $config;

    /**
     * @var BeijingCaApi
     */
    protected $api;

    /**
     * @param array{
     *           baseUrl: string,
     *           appId: string,
     *           deviceId: string,
     *           hmacKey: string,
     *           sm4KeyId: string,
     *           hmacKeyId: string,
     *      } $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->api = new BeijingCaApi($config);
    }

    /**
     * 计算HMAC-SM3哈希值
     * @param string $text   明文字符串
     * @return string        哈希字符串，base64编码
     * @throws SmCryptorException
     */
    public function hmacSm3(string $text): string
    {
        if (!$text) {
            return '';
        }

        try {
            $source = base64_encode($text);
            $result = $this->api->hmacSm3($source, $this->config['hmacKeyId']);
            return $result['hmac'];
        } catch (HttpRequestException $e) {
            Log::error(__METHOD__, $e->errors());
            throw new SmCryptorException($e->getMessage());
        }
    }

    /**
     * sm4加密
     * @param string $text  明文字符串
     * @return string       密文字符串，base64编码
     * @throws SmCryptorException
     */
    public function sm4Encrypt(string $text): string
    {
        if (!$text) {
            return '';
        }

        try {
            $plainText = base64_encode($text);
            $result = $this->api->encrypt($plainText, $this->config['sm4KeyId']);
            return $result['cipherTextBlob'];
        } catch (HttpRequestException $e) {
            Log::error(__METHOD__, $e->errors());
            throw new SmCryptorException($e->getMessage());
        }
    }

    /**
     * sm4解密
     * @param string $cipherText   密文字符串，base64编码
     * @return string              明文字符串
     * @throws SmCryptorException
     */
    public function sm4Decrypt(string $cipherText): string
    {
        if (!$cipherText) {
            return '';
        }

        try {
            $result = $this->api->decrypt($cipherText, $this->config['sm4KeyId']);
            return base64_decode($result['plainText']);
        } catch (HttpRequestException $e) {
            Log::error(__METHOD__, $e->errors());
            throw new SmCryptorException($e->getMessage());
        }
    }
}
