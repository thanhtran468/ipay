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
use IPay\Builders\RequestBodyBuilder;
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
 * @phpstan-import-type ParametersType from RequestBodyBuilder
 */
final class IPayClient extends AbstractApi
{
    use LazyGhostTrait {
        createLazyGhost as private;
    }

    /**
     * @throws Exceptions\LoginException
     */
    public static function fromCredentials(string $username, string $password): AbstractApi
    {
        $client = (new self(
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
        ))->login($username, $password);

        return $client;
    }

    /**
     * @param ParametersType $authenticatedParameters
     */
    private function __construct(
        private HttpMethodsClientInterface $client,
        private array $authenticatedParameters = [],
        private ObjectMapper $objectMapper = new ObjectMapperUsingReflection(
            new DefinitionProvider(
                keyFormatter: new KeyFormatterWithoutConversion(),
            ),
        ),
    ) {
        if ($authenticatedParameters) {
            self::createLazyGhost(
                initializer: $this->populateLazyProperties(...),
                instance: $this,
            );
        }
    }

    private function login(string $userName, string $accessCode): self
    {
        /** @var array{sessionId: string, ...} */
        $response = $this->post('signIn', get_defined_vars() + $this->bypassCaptcha());

        return new self($this->client, ['sessionId' => $response['sessionId']]);
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
            RequestBodyBuilder::new()
                ->enhance($this->getRequiredParameters())
                ->build($parameters)
                ->encrypt(),
        );

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @return ParametersType
     */
    private function getRequiredParameters(): array
    {
        return array_merge([
            'lang' => 'en',
            'requestId' => Random::generate(12, '0-9A-Z').'|'.time(),
        ], $this->authenticatedParameters);
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
