<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\Components\Interfaces;

use SymfonyBundle\UIBundle\Foundation\Core\Contract\OutputContractInterface;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\Locale;

interface QueryContextInterface
{
    public function hasLocale(): bool;
    public function getLocale(): ?Locale;
    public function setLocale(Locale $locale): self;

    /**
     * @return class-string<OutputContractInterface>
     */
    public function getOutputDtoClass(): string;

    /**
     * @param class-string<OutputContractInterface> $outputDtoClass
     * @return self
     */
    public function setOutputDtoClass(string $outputDtoClass): self;
}
