<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\Contract\Filter;

use SymfonyBundle\UIBundle\Query\Core\Contract\Filter\Traits\FilterContractTrait;
use SymfonyBundle\UIBundle\Query\Core\Contract\Filter\Traits\PaginationContractTrait;
use SymfonyBundle\UIBundle\Query\Core\Contract\Filter\Traits\SortContractTrait;

class FilterSortPagination
{
    use FilterContractTrait;
    use SortContractTrait;
    use PaginationContractTrait;
}
