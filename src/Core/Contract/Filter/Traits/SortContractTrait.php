<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\Contract\Filter\Traits;

use OpenApi\Annotations as OA;

trait SortContractTrait
{
    /**
     * @OA\Property(type="string", example="-createdAt,updatedAt")
     */
    public string $sort;
}
