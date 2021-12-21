<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\CQRS\Query\Search;

use SymfonyBundle\UIBundle\Foundation\Core\Components\AbstractContext;
use SymfonyBundle\UIBundle\Foundation\Core\Contract\OutputContractInterface;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\Locale;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\PropertyNameConvertDto;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\TranslationDto;
use SymfonyBundle\UIBundle\Query\Core\Components\Interfaces\QueryContextInterface;
use SymfonyBundle\UIBundle\Query\Core\Dto\Filters;
use SymfonyBundle\UIBundle\Query\Core\Dto\Sorts;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\Pagination;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\SearchQuery;
use Closure;

class Context extends AbstractContext implements QueryContextInterface
{
    /** @var class-string */
    protected string $targetEntityClass;
    /** @var class-string<OutputContractInterface> */
    protected string $outputDtoClass;
    protected array $filterBlackList;
    protected ?Closure $entityCallback;
    protected array $filterAliases;
    protected array $translations;
    protected ?Locale $locale;
    protected Pagination $pagination;
    protected Filters $filters;
    protected Sorts $sorts;
    protected bool $eager;
    protected string $outputFormat;
    protected array $relations;
    protected ?Closure $outputDataPrepareCallback;

    /**
     * Context constructor.
     * @param class-string $targetEntityClass
     * @param string $outputFormat
     * @param class-string<OutputContractInterface> $outputDtoClass
     * @param array $filterBlackList
     * @param Closure|null $entityCallback
     * @param array $filterAliases
     * @param array $translations
     * @param array $relations
     * @param Locale|null $locale
     * @param Pagination|null $pagination
     * @param Filters|null $filters
     * @param Sorts|null $sorts
     * @param bool $eager
     */
    public function __construct(
        string $targetEntityClass,
        string $outputFormat,
        string $outputDtoClass,
        array $filterBlackList = [],
        Closure $entityCallback = null,
        array $filterAliases = [],
        array $translations = [],
        array $relations = [],
        ?Closure $outputDataPrepareCallback = null,
        Locale $locale = null,
        Pagination $pagination = null,
        Filters $filters = null,
        Sorts $sorts = null,
        bool $eager = true,
    ) {
        $this->targetEntityClass = $targetEntityClass;
        $this->outputDtoClass = $outputDtoClass;
        $this->filterBlackList = $filterBlackList;
        $this->entityCallback = $entityCallback;
        $this->filterAliases = $filterAliases;
        $this->translations = $translations;
        $this->locale = $locale;
        $this->pagination = $pagination ?? new Pagination();
        $this->filters = $filters ?? new Filters();
        $this->sorts = $sorts ?? new Sorts();
        $this->eager = $eager;
        $this->outputFormat = $outputFormat;
        $this->relations = $relations;
        $this->outputDataPrepareCallback = $outputDataPrepareCallback;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function setOutputFormat(string $outputFormat): self
    {
        $this->outputFormat = $outputFormat;
        return $this;
    }

    public function getFilterBlackList(): array
    {
        return $this->filterBlackList;
    }

    public function setFilterBlackList(array $filterBlackList): self
    {
        $this->filterBlackList = $filterBlackList;
        return $this;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function setTranslations(TranslationDto $translations): self
    {
        $this->translations = $translations->getRules();
        return $this;
    }

    public function getFilterAliases(): array
    {
        return $this->filterAliases;
    }

    public function setFilterAliases(PropertyNameConvertDto $filterAliases): self
    {
        $this->filterAliases = $filterAliases->getRules();
        return $this;
    }

    public function getFilters(): Filters
    {
        return $this->filters;
    }

    public function setFilters(Filters $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function getSorts(): Sorts
    {
        return $this->sorts;
    }

    public function setSorts(Sorts $sorts): self
    {
        $this->sorts = $sorts;
        return $this;
    }

    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    public function setPagination(Pagination $pagination): self
    {
        $this->pagination = $pagination;
        return $this;
    }

    public function setSearchQuery(SearchQuery $searchQuery): self
    {
        $this->pagination = $searchQuery->getPagination();
        $this->sorts = $searchQuery->getSorts();
        $this->filters = $searchQuery->getFilters();
        return $this;
    }

    /**
     * @return class-string
     */
    public function getTargetEntityClass(): string
    {
        return $this->targetEntityClass;
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

    public function getEntityCallback(): ?Closure
    {
        return $this->entityCallback;
    }

    #todo: добавить возможность работать не с одним, а с несколькими колбэками
    public function setEntityCallback(Closure $entityCallback): self
    {
        $this->entityCallback = $entityCallback;
        return $this;
    }

    public function getEagerMode(): bool
    {
        return $this->eager;
    }

    public function setEagerMode(bool $eager): self
    {
        $this->eager = $eager;
        return $this;
    }

    public function hasLocale(): bool
    {
        return $this->locale !== null;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    public function setRelations(array $relations): void
    {
        $this->relations = $relations;
    }

    public function getOutputDataPrepareCallback(): ?Closure
    {
        return $this->outputDataPrepareCallback;
    }

    public function setOutputDataPrepareCallback(?Closure $outputDataPrepareCallback): void
    {
        $this->outputDataPrepareCallback = $outputDataPrepareCallback;
    }
}
