<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\Contract\Filter\Traits;

use OpenApi\Annotations as OA;

trait FilterContractTrait
{
    /**
     * @OA\Property(type="object", example={"propertyName_1": {"like": "value_1"}, "propertyName_2": {"eq": "value_2"}})
     */
    public array $filter;
}
