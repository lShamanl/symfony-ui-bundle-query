<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\CQRS\Query\Aggregate;

use SymfonyBundle\UIBundle\Foundation\Core\Components\AbstractContext;
use SymfonyBundle\UIBundle\Foundation\Core\Contract\OutputContractInterface;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\Locale;
use SymfonyBundle\UIBundle\Query\Core\Components\Interfaces\QueryContextInterface;
use SymfonyBundle\UIBundle\Query\Core\Dto\Filters;
use SymfonyBundle\UIBundle\Query\Core\Dto\Sorts;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\Pagination;

class Context extends AbstractContext implements QueryContextInterface
{
    protected string $aggregateId;
    /** @var class-string<OutputContractInterface> */
    protected string $outputDtoClass;
    protected ?Locale $locale;
    /** @var class-string */
    protected string $targetEntityClass;
    protected array $translations;
    protected Filters $filters;
    protected string $outputFormat;

    public function __construct(
        string $aggregateId,
        string $outputFormat,
        string $outputDtoClass,
        string $targetEntityClass,
        array $translations = [],
        Locale $locale = null,
        Filters $filters = null
    ) {
        $this->targetEntityClass = $targetEntityClass;
        $this->outputDtoClass = $outputDtoClass;
        $this->translations = $translations;
        $this->locale = $locale;
        $this->filters = $filters ?? new Filters();
        $this->outputFormat = $outputFormat;
        $this->aggregateId = $aggregateId;
    }

    public function getFilters(): Filters
    {
        return $this->filters;
    }

    public function setFilters(Filters $filters): void
    {
        $this->filters = $filters;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function setOutputFormat(string $outputFormat): void
    {
        $this->outputFormat = $outputFormat;
    }

    public function getTargetEntityClass(): string
    {
        return $this->targetEntityClass;
    }

    public function setTargetEntityClass(string $targetEntityClass): void
    {
        $this->targetEntityClass = $targetEntityClass;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    public function hasLocale(): bool
    {
        return $this->locale !== null;
    }

    /**
     * @return class-string<OutputContractInterface>
     */
    public function getOutputDtoClass(): string
    {
        return $this->outputDtoClass;
    }

    public function setOutputDtoClass(string $outputDtoClass): self
    {
        $this->outputDtoClass = $outputDtoClass;
        return $this;
    }

    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function setAggregateId(string $aggregateId): void
    {
        $this->aggregateId = $aggregateId;
    }
}
