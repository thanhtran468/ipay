<?php

namespace IPay\Api;

/**
 * @extends AbstractApi<UnauthenticatedSession>
 */
class UnauthenticatedApi extends AbstractApi
{
    /**
     * @param array{
     *      userName: string,
     *      accessCode: string,
     *      captchaCode: string,
     *      captchaId: string,
     * } $credentials
     */
    public function login(array $credentials): AuthenticatedApi
    {
        $resolver = self::createOptionsResolver()
            ->setRequired([
                'userName',
                'accessCode',
                'captchaCode',
                'captchaId',
            ])
            ->setAllowedTypes('userName', 'string')
            ->setAllowedTypes('accessCode', 'string')
            ->setAllowedTypes('captchaCode', 'string')
            ->setAllowedTypes('captchaId', 'string')
        ;

        /** @var array{sessionId: string, ...} */
        $result = $this->post('/signIn', $resolver->resolve($credentials));

        return new AuthenticatedApi(
            $this->iPayClient,
            new AuthenticatedSession($result['sessionId'])
        );
    }
}
