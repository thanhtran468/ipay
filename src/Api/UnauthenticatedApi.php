<?php

namespace IPay\Api;

use IPay\Captcha\CaptchaSolver;
use Nette\Utils\Random;

/**
 * @extends AbstractApi<UnauthenticatedSession>
 */
final class UnauthenticatedApi extends AbstractApi
{
    /**
     * @param array{
     *      userName: string,
     *      accessCode: string,
     * } $credentials
     *
     * @throws \IPay\Exception\LoginException
     */
    public function login(array $credentials): AuthenticatedApi
    {
        $resolver = self::createOptionsResolver()
            ->setRequired([
                'userName',
                'accessCode',
            ])
            ->setAllowedTypes('userName', 'string')
            ->setAllowedTypes('accessCode', 'string')
        ;

        $parameters = $resolver->resolve($credentials) + $this->bypassCaptcha();

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
