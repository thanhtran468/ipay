<?php

namespace IPay\Api;

use IPay\Captcha\CaptchaSolver;
use IPay\Session\AuthenticatedSession;
use IPay\Session\UnauthenticatedSession;
use Nette\Utils\Random;

/**
 * @internal
 *
 * @extends AbstractApi<UnauthenticatedSession>
 */
final class UnauthenticatedApi extends AbstractApi
{
    public function login(string $userName, string $accessCode): AuthenticatedApi
    {
        $parameters = get_defined_vars() + $this->bypassCaptcha();

        /** @var array{sessionId: string, ...} */
        $result = $this->post('signIn', $parameters);

        return new AuthenticatedApi(
            $this->iPayClient,
            new AuthenticatedSession($result['sessionId'])
        );
    }

    /**
     * @return array{captchaId:string,captchaCode:string}
     */
    private function bypassCaptcha(): array
    {
        $captchaId = Random::generate(9, '0-9a-zA-Z');
        $svg = (string) $this->iPayClient->getClient()
            ->get(sprintf('api/get-captcha/%s', $captchaId))
            ->getBody();
        $captchaCode = CaptchaSolver::solve($svg);

        return compact('captchaId', 'captchaCode');
    }
}
