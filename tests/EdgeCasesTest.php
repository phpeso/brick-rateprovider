<?php

declare(strict_types=1);

namespace Peso\Brick\Tests;

use Arokettu\Date\Date;
use Brick\Money\CurrencyConverter;
use Brick\Money\Money;
use Error;
use Peso\Brick\PesoRateProvider;
use Peso\Core\Responses\ConversionResponse;
use Peso\Core\Services\PesoServiceInterface;
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

        $exchange = new PesoRateProvider($service);
        $converter = new CurrencyConverter($exchange);

        $this->expectException(Error::class);
        $this->expectExceptionMessage(
            'Broken Service object: the response must be an instance of ExchangeRateResponse|ErrorResponse',
        );

        $converter->convert(Money::of(100, 'EUR'), 'USD');
    }
}
