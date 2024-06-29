<?php

namespace IPay\Http\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
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
                /** @var object{errorCode:string} */
                $error = Json::decode((string) $response->getBody());

                throw self::createException($error->errorCode);
            }

            return $response;
        });
    }

    private static function createException(string $code): \Throwable
    {
        return match ($code) {
            '96', '99' => new SessionExpiredException('The session has expired.'),
            default => new \RuntimeException('Unknown error.'),
        };
    }
}
