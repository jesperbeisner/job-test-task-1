<?php

declare(strict_types=1);

namespace App\RequestValidator;

use App\Exception\ValidationErrorException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRequestValidator implements RequestValidatorInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @param mixed[] $requestBody
     * @return array{firstName: string, lastName: string, email: string, password: string}
     * @throws ValidationErrorException
     */
    public function validate(array $requestBody): array
    {
        $constraint = new Assert\Collection(
            [
                'firstName' => new Assert\Length([
                    'min' => 2,
                    'max' => 255,
                    'minMessage' => "The 'firstName' field value is too short. It should have {{ limit }} characters or more.",
                    'maxMessage' => "The 'firstName' field value is too long. It should have {{ limit }} characters or less.",
                ]),
                'lastName' => new Assert\Length([
                    'min' => 2,
                    'max' => 255,
                    'minMessage' => "The 'lastName' field value is too short. It should have {{ limit }} characters or more.",
                    'maxMessage' => "The 'lastName' field value is too long. It should have {{ limit }} characters or less.",
                ]),
                'email' => [
                    new Assert\NotBlank(message: "The 'email' field value should not be blank."),
                    new Assert\Email(message: "The 'email' field value is not a valid email address."),
                ],
                'password' => new Assert\Length([
                    'min' => 10,
                    'max' => 255,
                    'minMessage' => "The 'password' field value is too short. It should have {{ limit }} characters or more.",
                    'maxMessage' => "The 'password' field value is too long. It should have {{ limit }} characters or less.",
                ])
            ],
            extraFieldsMessage: "The field {{ field }} was not expected.",
            missingFieldsMessage: "The field {{ field }} is missing."
        );

        $constraintViolationList = $this->validator->validate($requestBody, $constraint);

        if (count($constraintViolationList) > 0) {
            $errors = [];

            /** @var ConstraintViolationInterface $constraintViolation */
            foreach ($constraintViolationList as $constraintViolation) {
                $errors[] = ['errorMessage' => str_replace('"', "'", (string) $constraintViolation->getMessage())];
            }

            throw new ValidationErrorException($errors);
        }

        /** @var array{firstName: string, lastName: string, email: string, password: string} $validatedRequestBody */
        $validatedRequestBody = $requestBody;

        return $validatedRequestBody;
    }
}
