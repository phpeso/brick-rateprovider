<?php

declare(strict_types=1);

namespace Peso\Brick\Tests;

use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\Money;
use Peso\Brick\PesoRateProvider;
use Peso\Core\Services\ArrayService;
use Peso\Core\Services\NullService;
use PHPUnit\Framework\TestCase;

final class PesoRateProviderTest extends TestCase
{
    public function testExchange(): void
    {
        $service = new ArrayService([
            'USD' => [
                'EUR' => '0.91234',
            ],
        ]);
        $rateProvider = new PesoRateProvider($service);
        $converter = new CurrencyConverter($rateProvider);

        $usd100 = Money::of(100.00, 'USD');

        $eur = $converter->convert($usd100, 'EUR', roundingMode: RoundingMode::HALF_EVEN);

        self::assertEquals('EUR 91.23', (string)$eur);
    }

    public function testUnresolvable(): void
    {
        $service = new ArrayService([
            'USD' => [
                'EUR' => '0.91234',
            ],
        ]);
        $rateProvider = new PesoRateProvider($service);
        $converter = new CurrencyConverter($rateProvider);

        $eur100 = Money::of(100.00, 'EUR');

        self::expectException(CurrencyConversionException::class);
        self::expectExceptionMessage(
            'No exchange rate available to convert EUR to USD (Unable to find exchange rate for EUR/USD)',
        );

        $converter->convert($eur100, 'USD');
    }

    public function testExchangeSame(): void
    {
        $rateProvider = new PesoRateProvider(new NullService());
        $converter = new CurrencyConverter($rateProvider);

        $eur100 = Money::of(100.00, 'EUR');
        $converted = $converter->convert($eur100, 'EUR');

        self::assertEquals($eur100, $converted);
    }
}
