<?php

declare(strict_types=1);

namespace App\Manager;

use App\Adapter\UserAdapterInterface;
use App\Entity\User;
use App\Exception\ValidationErrorException;
use App\RequestValidator\UserRequestValidator;
use DateTime;

class UserManager
{
    public function __construct(
        private readonly UserAdapterInterface $userAdapter,
        private readonly UserRequestValidator $userRequestValidator,
    ) {
    }

    public function findUser(string $id): ?User
    {
        return $this->userAdapter->findUser($id);
    }

    /**
     * @return User[]
     */
    public function findUsers(): array
    {
        return $this->userAdapter->findUsers();
    }

    /**
     * @param mixed[] $requestBody
     * @throws ValidationErrorException
     */
    public function createUser(array $requestBody): User
    {
        $validatedUserRequestBody = $this->userRequestValidator->validate($requestBody);

        // Extra quick and dirty check if the email is unique.
        // Would normally do this in the validator itself with a unique entity assertion.
        // But it's more difficult when you have database users AND json users.
        if (null !== $this->userAdapter->findUserByEmail($validatedUserRequestBody['email'])) {
            throw new ValidationErrorException([['errorMessage' => "A user with email address '{$validatedUserRequestBody['email']}' already exists."]]);
        }

        $user = new User();
        $user->setFirstName($validatedUserRequestBody['firstName']);
        $user->setLastName($validatedUserRequestBody['lastName']);
        $user->setEmail($validatedUserRequestBody['email']);
        $user->setPassword(password_hash($validatedUserRequestBody['password'], PASSWORD_DEFAULT));

        $this->userAdapter->createUser($user);

        return $user;
    }

    /**
     * @param mixed[] $requestBody
     * @throws ValidationErrorException
     */
    public function updateUser(array $requestBody, User $user): void
    {
        $validatedUserRequestBody = $this->userRequestValidator->validate($requestBody);

        // Extra quick and dirty check if the email is unique.
        // Would normally do this in the validator itself with a unique entity assertion.
        // But it's more difficult when you have database users AND json users.
        if (null !== $emailUser = $this->userAdapter->findUserByEmail($validatedUserRequestBody['email'])) {
            if ($user->getId() !== $emailUser->getId()) {
                throw new ValidationErrorException([['errorMessage' => "A user with email address '{$validatedUserRequestBody['email']}' already exists."]]);
            }
        }

        $user->setFirstName($validatedUserRequestBody['firstName']);
        $user->setLastName($validatedUserRequestBody['lastName']);
        $user->setEmail($validatedUserRequestBody['email']);
        $user->setPassword(password_hash($validatedUserRequestBody['password'], PASSWORD_DEFAULT));
        $user->setUpdated(new DateTime());

        $this->userAdapter->updateUser($user);
    }

    public function deleteUser(User $user): void
    {
        $this->userAdapter->deleteUser($user);
    }
}
