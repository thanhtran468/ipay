<?php

namespace IPay\Http\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use IPay\Exception\LoginException;
use IPay\Exception\SessionException;
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
     * @param \stdClass&object{errorCode: string, errorMessage?: string} $error
     */
    private static function createException(\stdClass $error): \Throwable
    {
        /** @var class-string<\Exception> */
        $class = match ($error->errorCode) {
            'LOGON_CREDENTIALS_REJECTED' => LoginException::class,
            '96', '99' => SessionException::class,
            default => \RuntimeException::class,
        };

        return new $class($error->errorMessage ?? 'Unknown error.');
    }
}
