<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class ValidationErrorException extends Exception
{
    /**
     * @param array<int, array{errorMessage: string}> $errors
     */
    public function __construct(
        public readonly array $errors
    ) {
        parent::__construct();
    }
}
