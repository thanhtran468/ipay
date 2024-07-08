<?php

namespace IPay\Api;

use IPay\Captcha\CaptchaSolver;
use Nette\Utils\Random;

/**
 * @extends AbstractApi<UnauthenticatedSession>
 */
class UnauthenticatedApi extends AbstractApi
{
    /**
     * @param array{
     *      userName: string,
     *      accessCode: string,
     * } $credentials
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

        [$captchaId, $captchaCode] = $this->bypassCaptcha();

        $parameters = $resolver->resolve($credentials);
        $parameters['captchaId'] = $captchaId;
        $parameters['captchaCode'] = $captchaCode;

        /** @var array{sessionId: string, ...} */
        $result = $this->post('signIn', $parameters);

        return new AuthenticatedApi(
            $this->iPayClient,
            new AuthenticatedSession($result['sessionId'])
        );
    }

    /**
     * @return array{string,string}
     */
    private function bypassCaptcha(): array
    {
        $captchaId = Random::generate(9, '0-9a-zA-Z');
        $svg = (string) $this->iPayClient->getClient()
            ->get(sprintf('api/get-captcha/%s', $captchaId))
            ->getBody();

        return [$captchaId, CaptchaSolver::solve($svg)];
    }
}
