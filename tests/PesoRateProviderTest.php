<?php

/**
 * @copyright 2025 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Peso\Brick\Tests;

use Arokettu\Date\Calendar;
use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Brick\Money\Exception\ExchangeRateNotFoundException;
use Brick\Money\Money;
use DateTime;
use Peso\Brick\PesoRateProvider;
use Peso\Core\Services\ArrayService;
use Peso\Core\Services\NullService;
use PHPUnit\Framework\TestCase;
use stdClass;

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

        $usd100 = Money::of('100.00', 'USD');

        $eur = $converter->convert($usd100, 'EUR', roundingMode: RoundingMode::HalfEven);

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

        $eur100 = Money::of('100.00', 'EUR');

        self::expectException(ExchangeRateNotFoundException::class);
        self::expectExceptionMessage('No exchange rate available to convert EUR to USD');

        $converter->convert($eur100, 'USD');
    }

    public function testExchangeSame(): void
    {
        $rateProvider = new PesoRateProvider(new NullService());
        $converter = new CurrencyConverter($rateProvider);

        $eur100 = Money::of('100.00', 'EUR');
        $converted = $converter->convert($eur100, 'EUR');

        self::assertEquals($eur100, $converted);
    }

    public function testHistoricalExchange(): void
    {
        $service = new ArrayService(
            currentRates: [
                'USD' => [
                    'EUR' => '0.92143',
                ],
            ],
            historicalRates: [
                '2025-06-13' => [
                    'USD' => [
                        'EUR' => '0.91234',
                    ],
                ],
                '2025-06-19' => [
                    'USD' => [
                        'EUR' => '0.94321',
                    ],
                ],
            ],
        );
        $rateProvider = new PesoRateProvider($service);
        $converter = new CurrencyConverter($rateProvider);

        $usd100 = Money::of('100.00', 'USD');

        $eur = $converter->convert($usd100, 'EUR', roundingMode: RoundingMode::HalfEven);
        self::assertEquals('EUR 92.14', (string)$eur);

        // null date
        $eur = $converter->convert($usd100, 'EUR', dimensions: [
            'date' => null,
        ], roundingMode: RoundingMode::HalfEven);
        self::assertEquals('EUR 92.14', (string)$eur);


        /* Historical rates */

        // date as string
        $eur = $converter->convert($usd100, 'EUR', dimensions: [
            'date' => '2025-06-19',
        ], roundingMode: RoundingMode::HalfEven);
        self::assertEquals('EUR 94.32', (string)$eur);

        // date as an object
        $eur = $converter->convert($usd100, 'EUR', dimensions: [
            'date' => Calendar::parse('2025-06-19'),
        ], roundingMode: RoundingMode::HalfEven);
        self::assertEquals('EUR 94.32', (string)$eur);

        // date as a DT object
        $eur = $converter->convert($usd100, 'EUR', dimensions: [
            'date' => new DateTime('2025-06-19'),
        ], roundingMode: RoundingMode::HalfEven);
        self::assertEquals('EUR 94.32', (string)$eur);
    }

    public function testUnknownDimensions(): void
    {
        $service = new ArrayService(
            currentRates: [
                'USD' => [
                    'EUR' => '0.92143',
                ],
            ],
        );
        $rateProvider = new PesoRateProvider($service);
        $converter = new CurrencyConverter($rateProvider);

        $usd100 = Money::of('100.00', 'USD');

        self::expectException(ExchangeRateNotFoundException::class);
        self::expectExceptionMessage('No exchange rate available to convert USD to EUR');

        $converter->convert($usd100, 'EUR', dimensions: [
            'something' => null,
        ], roundingMode: RoundingMode::HalfEven);
    }

    public function testUnknownDateType(): void
    {
        $service = new ArrayService(
            currentRates: [
                'USD' => [
                    'EUR' => '0.92143',
                ],
            ],
        );
        $rateProvider = new PesoRateProvider($service);
        $converter = new CurrencyConverter($rateProvider);

        $usd100 = Money::of('100.00', 'USD');

        self::expectException(ExchangeRateNotFoundException::class);
        self::expectExceptionMessage('No exchange rate available to convert USD to EUR');

        $converter->convert($usd100, 'EUR', dimensions: [
            'date' => new stdClass(),
        ], roundingMode: RoundingMode::HalfEven);
    }
}
