<?php

declare(strict_types=1);

namespace App\RequestValidator;

interface RequestValidatorInterface
{
    /**
     * @param mixed[] $requestBody
     * @return mixed[]
     */
    public function validate(array $requestBody): array;
}
