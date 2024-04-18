<?php

namespace App\Helpers;

class CryptHelper
{
    public static function encryptNumber(int $number)
    {
        $chave = "aAzZ";
        $number += 100000;

        $result = '';
        $text = strval($number);
        for ($i = 0; $i < strlen($text); $i++) {
            $result .= chr(ord($text[$i]) ^ ord($chave[$i % strlen($chave)]));
        }
        return $result;
    }
    public static function decryptNumber(string $text)
    {
        $chave = "aAzZ";

        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $result .= chr(ord($text[$i]) ^ ord($chave[$i % strlen($chave)]));
        }
        $number = +$result - 100000;
        return $number;
    }
}
