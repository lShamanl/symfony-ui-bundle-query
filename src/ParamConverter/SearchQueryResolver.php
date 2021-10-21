<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\ParamConverter;

use SymfonyBundle\UIBundle\Query\Core\Components\Helpers\FiltersMaker;
use SymfonyBundle\UIBundle\Query\Core\Components\Helpers\PaginationMaker;
use SymfonyBundle\UIBundle\Query\Core\Components\Helpers\SortsMaker;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\SearchQuery;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SearchQueryResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === SearchQuery::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        yield new SearchQuery(
            PaginationMaker::make($request),
            FiltersMaker::make($request),
            SortsMaker::make($request)
        );
    }
}
