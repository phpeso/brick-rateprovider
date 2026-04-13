<?php

/**
 * @copyright 2025 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Peso\Brick\Tests;

use Arokettu\Date\Calendar;
use Arokettu\Date\Date;
use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Brick\Money\Exception\ExchangeRateNotFoundException;
use Brick\Money\Money;
use DateTime;
use Peso\Brick\PesoHistoricalRateProvider;
use Peso\Core\Services\ArrayService;
use Peso\Core\Services\NullService;
use PHPUnit\Framework\TestCase;

final class PesoHistoricalRateProviderTest extends TestCase
{
    public function testExchange(): void
    {
        $service = new ArrayService(historicalRates: [
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
        ]);
        $rateProvider = new PesoHistoricalRateProvider($service, Calendar::parse('2025-06-13'));
        $converter = new CurrencyConverter($rateProvider);

        $usd100 = Money::of('100.00', 'USD');

        $eur = $converter->convert($usd100, 'EUR', roundingMode: RoundingMode::HalfEven);
        self::assertEquals('EUR 91.23', (string)$eur);

        $rateProvider = $rateProvider->withDate(Calendar::parse('2025-06-19'));
        $converter = new CurrencyConverter($rateProvider);

        $eur = $converter->convert($usd100, 'EUR', roundingMode: RoundingMode::HalfEven);
        self::assertEquals('EUR 94.32', (string)$eur);
    }

    public function testUnresolvable(): void
    {
        $service = new ArrayService(historicalRates: [
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
        ]);
        $rateProvider = new PesoHistoricalRateProvider($service, Calendar::parse('2025-06-14'));
        $converter = new CurrencyConverter($rateProvider);

        $usd100 = Money::of('100.00', 'USD');

        self::expectException(ExchangeRateNotFoundException::class);
        self::expectExceptionMessage('No exchange rate available to convert USD to EUR');

        $converter->convert($usd100, 'EUR');
    }

    public function testExchangeSame(): void
    {
        $rateProvider = new PesoHistoricalRateProvider(new NullService(), Date::today());
        $converter = new CurrencyConverter($rateProvider);

        $eur100 = Money::of('100.00', 'EUR');
        $converted = $converter->convert($eur100, 'EUR');

        self::assertEquals($eur100, $converted);
    }

    public function testHistoricalExchange(): void
    {
        // 0.13: Historical provider respects the dimension date but reverts to internal date on no date specified

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
        $rateProvider = new PesoHistoricalRateProvider($service, Calendar::parse('2025-06-13'));
        $converter = new CurrencyConverter($rateProvider);

        $usd100 = Money::of('100.00', 'USD');

        $eur = $converter->convert($usd100, 'EUR', roundingMode: RoundingMode::HalfEven);
        self::assertEquals('EUR 91.23', (string)$eur);

        // null date
        $eur = $converter->convert($usd100, 'EUR', dimensions: [
            'date' => null,
        ], roundingMode: RoundingMode::HalfEven);
        self::assertEquals('EUR 91.23', (string)$eur);


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
}
