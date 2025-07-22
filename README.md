# Peso-based RateProvider class for Brick\Money

[![Packagist]][Packagist Link]
[![PHP]][Packagist Link]
[![License]][License Link]
[![GitHub Actions]][GitHub Actions Link]
[![Codecov]][Codecov Link]

[Packagist]: https://img.shields.io/packagist/v/peso/brick-rateprovider.svg?style=flat-square
[PHP]: https://img.shields.io/packagist/php-v/peso/brick-rateprovider.svg?style=flat-square
[License]: https://img.shields.io/packagist/l/peso/brick-rateprovider.svg?style=flat-square
[GitHub Actions]: https://img.shields.io/github/actions/workflow/status/phpeso/brick-rateprovider/ci.yml?style=flat-square
[Codecov]: https://img.shields.io/codecov/c/gh/phpeso/brick-rateprovider?style=flat-square

[Packagist Link]: https://packagist.org/packages/peso/brick-rateprovider
[GitHub Actions Link]: https://github.com/phpeso/brick-rateprovider/actions
[Codecov Link]: https://codecov.io/gh/phpeso/brick-rateprovider
[License Link]: LICENSE.md

This is a library that provides a RateProvider class for the [Brick\Money] based on the Peso for PHP.

[Brick\Money]: https://github.com/brick/money

## Installation

```bash
composer require peso/brick-rateprovider
```

## Example

```php
<?php

use Brick\Money\CurrencyConverter;
use Brick\Money\Money;
use Peso\Brick\PesoRateProvider;
use Peso\Services\EuropeanCentralBankService;

require __DIR__ . '/vendor/autoload.php';

$rateProvider = new PesoRateProvider(new EuropeanCentralBankService());
$converter = new CurrencyConverter($rateProvider);

$eur100 = Money::of(100.00, 'EUR');

echo $converter->convert($eur100, 'USD'), PHP_EOL; // 'USD ...'
```

## Documentation

Read the full documentation here: <https://phpeso.org/v1.x/integrations/brick-rateprovider.html>

## Support

Please file issues on our main repo at GitHub: <https://github.com/phpeso/brick-rateprovider/issues>

## License

The library is available as open source under the terms of the [MIT License][License Link].
