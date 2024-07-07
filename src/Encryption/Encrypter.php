<?php

namespace IPay\Encryption;

const IPAY_PUBLIC_KEY = <<<PUB
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDLenQHmHpaqYX4IrRVM8H1uB21
xWuY+clsvn79pMUYR2KwIEfeHcnZFFshjDs3D2ae4KprjkOFZPYzEWzakg2nOIUV
WO+Q6RlAU1+1fxgTvEXi4z7yi+n0Zs0puOycrm8i67jsQfHi+HgdMxCaKzHvbECr
+JWnLxnEl6615hEeMQIDAQAB
-----END PUBLIC KEY-----
PUB;

class Encrypter
{
    public static function encrypt(string $message): string
    {
        $mlen = strlen($message);

        $result = '';
        foreach (str_split(
            $message,
            (int) ceil($mlen / ceil($mlen / 86))
        ) as $part) {
            openssl_public_encrypt(
                $part,
                $part,
                IPAY_PUBLIC_KEY,
                OPENSSL_PKCS1_OAEP_PADDING
            );

            $result .= $part;
        }

        return base64_encode($result);
    }
}
