<?php

declare(strict_types=1);

namespace Payum\Ecommpay\Action\Api;

final class Signer
{
    const ALGORITHM = 'sha512';
    const ITEMS_DELIMITER = ';';
    const MAXIMUM_RECURSION_DEPTH = 3;

    /**
     * Generate signature
     * @param array  $params
     * @param string $secretKey
     * @param array  $ignoreParamKeys
     * @param bool   $doNotHash
     * @return string
     */
    public static function sign(array $params, string $secretKey, array $ignoreParamKeys = [], bool $doNotHash = false): string
    {
        $paramsPrepared = self::getParamsToSign($params, $ignoreParamKeys, 1);
        $stringToSign = implode(self::ITEMS_DELIMITER, $paramsPrepared);
        return $doNotHash
            ? $stringToSign
            : base64_encode(hash_hmac('sha512', $stringToSign, $secretKey, true))
            ;
    }

    /**
     * Get parameters to sign
     * @param array $params
     * @param array $ignoreParamKeys
     * @param int $currentLevel
     * @param string $prefix
     * @return array
     */
    private static function getParamsToSign(
        array $params,
        array $ignoreParamKeys = [],
        int $currentLevel = 1,
        string $prefix = ''
    ): array
    {
        $paramsToSign = [];
        foreach ($params as $key => $value) {
            if ((in_array($key, $ignoreParamKeys) && $currentLevel == 1)) {
                continue;
            }
            $paramKey = ($prefix ? $prefix . ':' : '') . $key;
            if (is_array($value)) {
                if ($currentLevel >= self::MAXIMUM_RECURSION_DEPTH) {
                    $paramsToSign[$paramKey] = (string)$paramKey.':';
                } else {
                    $subArray = self::getParamsToSign($value, $ignoreParamKeys, $currentLevel + 1, $paramKey);
                    $paramsToSign = array_merge($paramsToSign, $subArray);
                }
            } else {
                if (is_bool($value)) {
                    $value = $value ? '1' : '0';
                } else {
                    $value = (string)$value;
                }
                $paramsToSign[$paramKey] = (string)$paramKey.':'.$value;
            }
        }
        if ($currentLevel == 1) {
            ksort($paramsToSign, SORT_NATURAL);
        }
        return $paramsToSign;
    }
}
