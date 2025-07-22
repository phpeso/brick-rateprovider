<?php

declare(strict_types=1);

namespace Peso\Brick;

use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\ExchangeRateProvider;
use Error;
use Override;
use Peso\Core\Responses\ErrorResponse;
use Peso\Core\Responses\ExchangeRateResponse;
use Peso\Core\Services\PesoServiceInterface;

abstract readonly class AbstractRateProvider implements ExchangeRateProvider
{
    public function __construct(
        protected PesoServiceInterface $service,
    ) {
    }

    abstract protected function createRequest(string $sourceCurrencyCode, string $targetCurrencyCode): object;

    /**
     * @inheritDoc
     */
    #[Override]
    public function getExchangeRate(string $sourceCurrencyCode, string $targetCurrencyCode): string
    {
        $request = $this->createRequest($sourceCurrencyCode, $targetCurrencyCode);
        $response = $this->service->send($request);

        if ($response instanceof ExchangeRateResponse) {
            return $response->rate->value;
        }
        if ($response instanceof ErrorResponse) {
            throw CurrencyConversionException::exchangeRateNotAvailable(
                $sourceCurrencyCode,
                $targetCurrencyCode,
                $response->exception->getMessage(),
            );
        }

        throw new Error(
            'Broken Service object: the response must be an instance of ExchangeRateResponse|ErrorResponse',
        );
    }
}
