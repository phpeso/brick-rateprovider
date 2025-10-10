<?php

/**
 * @copyright 2025 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Peso\Brick;

use Override;
use Peso\Core\Requests\CurrentExchangeRateRequest;

final readonly class PesoRateProvider extends AbstractRateProvider
{
    #[Override]
    protected function createRequest(string $sourceCurrencyCode, string $targetCurrencyCode): object
    {
        return new CurrentExchangeRateRequest($sourceCurrencyCode, $targetCurrencyCode);
    }
}
