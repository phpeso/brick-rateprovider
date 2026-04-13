<?php

/**
 * @copyright 2025 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Peso\Brick;

use Arokettu\Date\Calendar;
use Arokettu\Date\Date;
use Brick\Math\BigDecimal;
use Brick\Math\BigNumber;
use Brick\Money\Currency;
use Brick\Money\Exception\ExchangeRateProviderException;
use Brick\Money\ExchangeRateProvider;
use DateTimeInterface;
use Error;
use Override;
use Peso\Core\Exceptions\RuntimeException;
use Peso\Core\Requests\CurrentExchangeRateRequest;
use Peso\Core\Requests\HistoricalExchangeRateRequest;
use Peso\Core\Responses\ErrorResponse;
use Peso\Core\Responses\ExchangeRateResponse;
use Peso\Core\Services\PesoServiceInterface;

abstract readonly class AbstractRateProvider implements ExchangeRateProvider
{
    public function __construct(
        protected PesoServiceInterface $service,
    ) {
    }

    protected function createRequest(
        string $sourceCurrencyCode,
        string $targetCurrencyCode,
        array $dimensions = [],
    ): object|null {
        $date = false;

        foreach ($dimensions as $dimension => $value) {
            if ($dimension !== 'date') {
                return null;
            }
            if ($value === null) {
                continue; // allow explicitly set null
            }
            if ($value instanceof Date) {
                $date = $value;
            }
            if ($value instanceof DateTimeInterface) {
                $date = Calendar::fromDateTime($value);
            }
            if (\is_string($value)) {
                $date = Calendar::parse($value);
            }
        }

        return $date ?
            new HistoricalExchangeRateRequest($sourceCurrencyCode, $targetCurrencyCode, $date) :
            new CurrentExchangeRateRequest($sourceCurrencyCode, $targetCurrencyCode);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getExchangeRate(
        Currency $sourceCurrency,
        Currency $targetCurrency,
        array $dimensions = [],
    ): BigNumber|null {
        $request = $this->createRequest($sourceCurrency->getCurrencyCode(), $targetCurrency->getCurrencyCode());
        try {
            $response = $this->service->send($request);
        } catch (RuntimeException $e) {
            throw new ExchangeRateProviderException($e->getMessage(), $e);
        }

        if ($response instanceof ExchangeRateResponse) {
            return BigDecimal::of($response->rate->value);
        }
        if ($response instanceof ErrorResponse) {
            return null;
        }

        throw new Error(
            'Broken Service object: the response must be an instance of ExchangeRateResponse|ErrorResponse',
        );
    }
}
