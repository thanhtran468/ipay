<?php

namespace IPay\Http\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use IPay\Exception\LoginFailedException;
use IPay\Exception\SessionExpiredException;
use Nette\Utils\Json;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ExceptionThrower implements Plugin
{
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $next($request)->then(function (ResponseInterface $response): ResponseInterface {
            if (200 !== $response->getStatusCode()) {
                $error = Json::decode((string) $response->getBody());

                throw self::createException($error);
            }

            return $response;
        });
    }

    /**
     * @param \stdClass&object{errorCode: string, errorMessage: string} $error
     */
    private static function createException(\stdClass $error): \Throwable
    {
        return match ($error->errorCode) {
            'LOGON_CREDENTIALS_REJECTED' => new LoginFailedException($error->errorMessage),
            '96', '99' => new SessionExpiredException('The session has expired.'),
            default => new \RuntimeException('Unknown error.'),
        };
    }
}
