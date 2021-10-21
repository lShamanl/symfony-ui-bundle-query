<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\ParamConverter;

use SymfonyBundle\UIBundle\Query\Core\Components\Helpers\SortsMaker;
use SymfonyBundle\UIBundle\Query\Core\Dto\Sorts;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SortsResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Sorts::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        yield SortsMaker::make($request);
    }
}
