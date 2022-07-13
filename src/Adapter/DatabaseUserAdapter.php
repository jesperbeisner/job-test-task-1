<?php

declare(strict_types=1);

namespace App\Adapter;

use App\Entity\User;
use App\Repository\UserRepository;

class DatabaseUserAdapter implements UserAdapterInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function findUser(string $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    public function findUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function createUser(User $user): void
    {
        $this->userRepository->add($user, true);
    }

    public function updateUser(User $user): void
    {
        $this->userRepository->add($user, true);
    }

    public function deleteUser(User $user): void
    {
        $this->userRepository->remove($user, true);
    }
}
