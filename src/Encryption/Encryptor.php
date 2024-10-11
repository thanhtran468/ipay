<?php

namespace IPay\Encryption;

use phpseclib\Crypt\RSA;

final class Encryptor
{
    private const IPAY_PUBLIC_KEY = <<<PUBLIC
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDLenQHmHpaqYX4IrRVM8H1uB21
xWuY+clsvn79pMUYR2KwIEfeHcnZFFshjDs3D2ae4KprjkOFZPYzEWzakg2nOIUV
WO+Q6RlAU1+1fxgTvEXi4z7yi+n0Zs0puOycrm8i67jsQfHi+HgdMxCaKzHvbECr
+JWnLxnEl6615hEeMQIDAQAB
-----END PUBLIC KEY-----
PUBLIC;

    public static function encrypt(string $message): string
    {
        $rsa = new RSA();
        $rsa->loadKey(static::IPAY_PUBLIC_KEY);

        return base64_encode($rsa->encrypt($message));
    }
}
