<?php

/**
 * @copyright 2025 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Peso\Brick\Tests;

use Arokettu\Date\Date;
use Brick\Money\CurrencyConverter;
use Brick\Money\Exception\ExchangeRateProviderException;
use Brick\Money\Money;
use Error;
use Peso\Brick\PesoRateProvider;
use Peso\Core\Responses\ConversionResponse;
use Peso\Core\Services\PesoServiceInterface;
use Peso\Core\Services\SDK\Exceptions\HttpFailureException;
use Peso\Core\Types\Decimal;
use PHPUnit\Framework\TestCase;

final class EdgeCasesTest extends TestCase
{
    public function testBrokenService(): void
    {
        $service = new class implements PesoServiceInterface
        {
            public function send(object $request): ConversionResponse
            {
                // the service must return ExchangeRateResponse for *ExchangeRateRequest
                return new ConversionResponse(new Decimal('1'), Date::today());
            }

            public function supports(object $request): bool
            {
                return true;
            }
        };

        $rateProvider = new PesoRateProvider($service);
        $converter = new CurrencyConverter($rateProvider);

        $this->expectException(Error::class);
        $this->expectExceptionMessage(
            'Broken Service object: the response must be an instance of ExchangeRateResponse|ErrorResponse',
        );

        $converter->convert(Money::of(100, 'EUR'), 'USD');
    }

    public function testNetworkError(): void
    {
        $service = new class implements PesoServiceInterface
        {
            public function send(object $request): never
            {
                throw new HttpFailureException('HTTP Failure');
            }

            public function supports(object $request): bool
            {
                return true;
            }
        };

        $rateProvider = new PesoRateProvider($service);
        $converter = new CurrencyConverter($rateProvider);

        $this->expectException(ExchangeRateProviderException::class);
        $this->expectExceptionMessage('HTTP Failure');

        $converter->convert(Money::of(100, 'EUR'), 'USD');
    }
}
