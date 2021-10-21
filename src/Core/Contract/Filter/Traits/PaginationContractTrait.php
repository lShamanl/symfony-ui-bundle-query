<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\Contract\Filter\Traits;

use OpenApi\Annotations as OA;

trait PaginationContractTrait
{
    /**
     * @OA\Property(type="object", example={"number": 1, "size": 20})
     */
    public array $page;
}
