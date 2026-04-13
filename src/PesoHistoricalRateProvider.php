<?php

/**
 * @copyright 2025 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Peso\Brick;

use Arokettu\Date\Date;
use Override;
use Peso\Core\Services\PesoServiceInterface;

/**
 * @deprecated PesoRateProvider can do historical requests now
 * @see PesoRateProvider
 */
final readonly class PesoHistoricalRateProvider extends AbstractRateProvider
{
    public function __construct(
        PesoServiceInterface $service,
        private Date $date,
    ) {
        parent::__construct($service);
    }

    public function withDate(Date $date): self
    {
        return new self($this->service, $date);
    }

    #[Override]
    protected function createRequest(
        string $sourceCurrencyCode,
        string $targetCurrencyCode,
        array $dimensions,
    ): object|null {
        $dimensions['date'] ??= $this->date; // set date if not set by user
        return parent::createRequest($sourceCurrencyCode, $targetCurrencyCode, $dimensions);
    }
}
