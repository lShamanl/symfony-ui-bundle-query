<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\ParamConverter;

use SymfonyBundle\UIBundle\Query\Core\Components\Helpers\PaginationMaker;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\Pagination;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PaginationResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Pagination::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        yield PaginationMaker::make($request);
    }
}
