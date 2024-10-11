<?php

namespace IPay;

use EventSauce\ObjectHydrator\DefinitionProvider;
use EventSauce\ObjectHydrator\KeyFormatterWithoutConversion;
use EventSauce\ObjectHydrator\ObjectMapper;
use EventSauce\ObjectHydrator\ObjectMapperUsingReflection;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\ContentTypePlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use IPay\Builders\BodyBuilder;
use IPay\Builders\TransactionBuilder;
use IPay\Captcha\CaptchaSolver;
use IPay\Contracts\AbstractApi;
use IPay\Http\Plugins\ExceptionThrower;
use IPay\ValueObjects\Account;
use IPay\ValueObjects\Customer;
use IPay\ValueObjects\Transaction;
use Nette\Utils\Json;
use Nette\Utils\Random;
use Symfony\Component\VarExporter\LazyGhostTrait;

/**
 * @phpstan-import-type ParametersType from BodyBuilder
 */
final class IPayClient extends AbstractApi
{
    use LazyGhostTrait;

    /**
     * @throws Exceptions\LoginException
     */
    public static function fromCredentials(string $username, string $password): AbstractApi
    {
        return new self(
            $username,
            $password,
            new HttpMethodsClient(
                new PluginClient(Psr18ClientDiscovery::find(), [
                    new BaseUriPlugin(
                        Psr17FactoryDiscovery::findUriFactory()
                            ->createUri('https://api-ipay.vietinbank.vn')
                    ),
                    new ContentTypePlugin(),
                    new ExceptionThrower(),
                ]),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
            ),
        );
    }

    private function __construct(
        string $username,
        string $password,
        private HttpMethodsClientInterface $client,
        private BodyBuilder $bodyBuilder = new BodyBuilder(),
        private ObjectMapper $objectMapper = new ObjectMapperUsingReflection(
            new DefinitionProvider(
                keyFormatter: new KeyFormatterWithoutConversion(),
            ),
        ),
    ) {
        /** @var array{sessionId: string, ...} */
        $response = $this->post('signIn', [
            'userName' => $username,
            'accessCode' => $password,
        ] + $this->bypassCaptcha());
        $bodyBuilder->setSessionId($response['sessionId']);
        self::createLazyGhost(
            initializer: $this->populateLazyProperties(...),
            instance: $this,
        );
    }

    private function populateLazyProperties(): void
    {
        $this->customer = $this->objectMapper->hydrateObject(
            Customer::class,
            $this->post('getCustomerDetails')['customerInfo'],
        );
        $this->accounts = $this->objectMapper->hydrateObjects(
            Account::class,
            $this->post('getEntitiesAndAccounts')['accounts'],
        )->toArray();
    }

    /**
     * @suppress 1416
     */
    public function transactions(?string $accountNumber = null): TransactionBuilder
    {
        $that = $this;

        return (\Closure::bind(function () use ($that, $accountNumber): TransactionBuilder {
            $builder = new TransactionBuilder();
            $builder->resolver = \Closure::bind(function (array $parameters): \Traversable {
                return $this->getTransactions($parameters);
            }, $that, $that::class);
            $builder->parameters['accountNumber'] = $accountNumber ?? $that->customer->accountNumber;

            return $builder;
        }, null, TransactionBuilder::class))();
    }

    /**
     * @param ParametersType $parameters
     *
     * @return \Traversable<int, Transaction>
     *
     * @throws Exceptions\SessionException
     */
    private function getTransactions(array $parameters): \Traversable
    {
        $parameters['pageNumber'] = 0;
        do {
            $transactions = $this->post(
                'getHistTransactions',
                $parameters
            )['transactions'];
            foreach ($this->objectMapper->hydrateObjects(
                Transaction::class,
                $transactions
            )->getIterator() as $transaction) {
                yield $transaction;
            }
            ++$parameters['pageNumber'];
        } while (count($transactions) > 0);
    }

    /**
     * @param ParametersType $parameters
     *
     * @return mixed[]
     */
    private function post(string $uri, array $parameters = []): array
    {
        $response = $this->client->post(
            sprintf('ipay/wa/%s', $uri),
            [],
            $this->bodyBuilder->build($parameters)->encrypt(),
        );

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @return array{captchaId:string,captchaCode:string}
     */
    private function bypassCaptcha(): array
    {
        $captchaId = Random::generate(9, '0-9a-zA-Z');
        $svg = (string) $this->client
            ->get(sprintf('api/get-captcha/%s', $captchaId))
            ->getBody();
        $captchaCode = CaptchaSolver::solve($svg);

        return compact('captchaId', 'captchaCode');
    }
}
