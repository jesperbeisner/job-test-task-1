<?php

declare(strict_types=1);

namespace App\Adapter;

use App\Entity\User;
use DateTime;
use JsonException;
use RuntimeException;

class JsonUserAdapter implements UserAdapterInterface
{
    public function __construct(
        private readonly string $usersJsonFile
    ) {
    }

    public function findUser(string $id): ?User
    {
        foreach ($this->getUsers() as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }

        return null;
    }

    public function findUserByEmail(string $email): ?User
    {
        foreach ($this->getUsers() as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }

        return null;
    }

    public function findUsers(): array
    {
        return $this->getUsers();
    }

    public function createUser(User $user): void
    {
        $users = $this->getUsers();
        $users[] = $user;

        $this->writeUsers($users);
    }

    public function updateUser(User $user): void
    {
        $users = $this->getUsers();

        foreach ($users as $existingUser) {
            if ($existingUser->getId() === $user->getId()) {
                $existingUser->setFirstName($user->getFirstName());
                $existingUser->setLastName($user->getLastName());
                $existingUser->setEmail($user->getEmail());
                $existingUser->setPassword($user->getPassword());
                $existingUser->setCreated($user->getCreated());
                $existingUser->setUpdated($user->getUpdated());
            }
        }

        $this->writeUsers($users);
    }

    public function deleteUser(User $user): void
    {
        $users = $this->getUsers();

        foreach ($users as $key => $existingUser) {
            if ($existingUser->getId() === $user->getId()) {
                unset($users[$key]);
            }
        }

        $this->writeUsers($users);
    }

    /**
     * @return User[]
     * @throws JsonException
     */
    private function getUsers(): array
    {
        if (!file_exists($this->usersJsonFile)) {
            if (false === file_put_contents($this->usersJsonFile, json_encode([]))) {
                throw new RuntimeException("Could not write to file '$this->usersJsonFile'.");
            }
        }

        if (false === $content = file_get_contents($this->usersJsonFile)) {
            throw new RuntimeException("Could not read file '$this->usersJsonFile'.");
        }

        /** @var array<int, array{
         *     id: string,
         *     firstName: string,
         *     lastName: string,
         *     email: string,
         *     password: string,
         *     created: string,
         *     updated: string|null
         * }> $usersArray
         */
        $usersArray = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $users = [];
        foreach ($usersArray as $userData) {
            $user = new User();
            $user->setId($userData['id']);
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setEmail($userData['email']);
            $user->setPassword($userData['password']);
            $user->setCreated(new DateTime($userData['created']));
            $user->setUpdated($userData['updated'] === null ? null : new DateTime($userData['updated']));

            $users[] = $user;
        }

        return $users;
    }

    /**
     * @param User[] $users
     */
    public function writeUsers(array $users): void
    {
        $userData = [];
        foreach ($users as $user) {
            $userData[] = $user->toArray();
        }

        if (false === file_put_contents($this->usersJsonFile, json_encode($userData))) {
            throw new RuntimeException("Could not write to file '$this->usersJsonFile'.");
        }
    }
}
