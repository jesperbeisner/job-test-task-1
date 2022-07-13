<?php

declare(strict_types=1);

namespace App\Adapter;

use App\Entity\User;

interface UserAdapterInterface
{
    public function findUser(string $id): ?User;

    public function findUserByEmail(string $email): ?User;

    /**
     * @return User[]
     */
    public function findUsers(): array;

    public function createUser(User $user): void;

    public function updateUser(User $user): void;

    public function deleteUser(User $user): void;
}
