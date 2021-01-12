<?php

namespace app\api\common\library;

class Aes
{

    /**
     * @name:
     * @author:      gz
     * @description:
     * @param string $string 需要加密的字符串
     * @return string
     */
    public static function encrypt($string)
    {
        $key = self::getSecretKey();
        // openssl_encrypt 加密不同Mcrypt，对秘钥长度要求，超出16加密结果不变
        $data = openssl_encrypt($string, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        return base64_encode($data);
    }

    /**
     * @name:
     * @author:      gz
     * @description:
     * @param string $string 需要解密的字符串
     * @return string
     */
    public static function decrypt($string)
    {
        $key = self::getSecretKey();
        return openssl_decrypt(base64_decode($string), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    }

    public static function getSecretKey()
    {
        
        return config('app.appkey');
    }
}
