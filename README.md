## 安装

```shell
composer require oh86/laravel-sm-beijing-ca
```

## 一、配置 `config/sm_cryptor.php`

```php
return [
    'driver' => env('SM_CRYPTOR_DRIVER', 'beijingCa'),

    'beijingCa' => [
        'baseUrl' => env('BEIJING_CA_BASEURL'),
        'appId' => env('BEIJING_CA_APPID'),
        'deviceId' => env('BEIJING_CA_DEVICE_ID'),
        'hmacKey' => env('BEIJING_CA_HMAC_KEY'),

        // 主密钥id（生成主密钥的时候不需要配置，待到加解密/哈希运算的时候需要配置）
        'sm4KeyId' => env('BEIJING_CA_SM4_KEY_ID'),
        'hmacKeyId' => env('BEIJING_CA_HMAC_KEY_ID'),
    ],
];
```

## 二、生成主密钥和配置主密钥

```shell
# 生成主密钥（可用于`sm4KeyId`也可以用于`hmacKeyId`）
php artisan beijingCa:genMainKey

```

配置主密钥（配置`.env`）

```dotenv
BEIJING_CA_BASEURL=http://xx.xx.xx.xx:10666
BEIJING_CA_APPID=APP_xxx
BEIJING_CA_DEVICE_ID=DEV_xxx
BEIJING_CA_HMAC_KEY=xxx
# 主密钥
BEIJING_CA_SM4_KEY_ID=xxx
BEIJING_CA_HMAC_KEY_ID=${BEIJING_CA_SM4_KEY_ID}

```
