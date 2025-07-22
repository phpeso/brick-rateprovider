<?php

declare(strict_types=1);

namespace Peso\Brick;

use Arokettu\Date\Date;
use Override;
use Peso\Core\Requests\HistoricalExchangeRateRequest;
use Peso\Core\Services\PesoServiceInterface;

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
    protected function createRequest(string $sourceCurrencyCode, string $targetCurrencyCode): object
    {
        return new HistoricalExchangeRateRequest($sourceCurrencyCode, $targetCurrencyCode, $this->date);
    }
}
