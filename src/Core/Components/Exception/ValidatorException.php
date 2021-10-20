<?php

declare(strict_types=1);

namespace Bundle\UIBundle\Core\Components\Exception;

use Throwable;

/**
 * UIBundleCommand
 */
class ValidatorException extends DomainException
{
    public function __construct(
        string $message = "",
        ?int $code = 400,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, (int) $code, $previous);
    }
}
