<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\Service\Filter;

use SymfonyBundle\UIBundle\Query\Core\Dto\Filters;
use SymfonyBundle\UIBundle\Query\Core\Dto\Sorts;

class SearchQuery
{
    private Pagination $pagination;
    private Filters $filters;
    private Sorts $sorts;

    public function __construct(
        Pagination $pagination,
        Filters $filters,
        Sorts $sorts
    ) {
        $this->pagination = $pagination;
        $this->filters = $filters;
        $this->sorts = $sorts;
    }

    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    public function getFilters(): Filters
    {
        return $this->filters;
    }

    public function getSorts(): Sorts
    {
        return $this->sorts;
    }

    public function addFilter(Filter $filter): void
    {
        $this->filters->add($filter);
    }

    public function addSort(Sort $sort): void
    {
        $this->sorts->add($sort);
    }

    public function setPagination(Pagination $pagination): void
    {
        $this->pagination = $pagination;
    }
}
