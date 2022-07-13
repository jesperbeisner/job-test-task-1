<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ValidationErrorException;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/api/v1/users/{id}', name: 'app_users_index', defaults: ['id' => ''], methods: ['GET'])]
    public function index(string $id, UserManager $userManager): JsonResponse
    {
        if ($id === '') {
            return $this->json(['status' => 'Success', 'data' => $userManager->findUsers()]);
        }

        if (null === $user = $userManager->findUser($id)) {
            return $this->json(['status' => 'Failure', 'message' => "A user with id '$id' could not be found."], 404);
        }

        return $this->json(['status' => 'Success', 'data' => $user]);
    }

    #[Route('/api/v1/users', name: 'app_users_create', methods: ['POST'])]
    public function create(Request $request, UserManager $userManager): JsonResponse
    {
        try {
            $requestBody = $request->toArray();
        } catch (JsonException $e) {
            return $this->json(['status' => 'Failure', 'message' => $e->getMessage()], 400);
        }

        try {
            $user = $userManager->createUser($requestBody);
        } catch (ValidationErrorException $e) {
            return $this->json(['status' => 'Failure', 'message' => 'Errors occurred during request validation.', 'errors' => $e->errors], 400);
        }

        return $this->json(['status' => 'Success', 'data' => $user], 201);
    }

    #[Route('/api/v1/users/{id}', name: 'app_users_update', methods: ['PUT'])]
    public function update(string $id, Request $request, UserManager $userManager): JsonResponse
    {
        if (null === $user = $userManager->findUser($id)) {
            return $this->json(['status' => 'Failure', 'message' => "A user with id '$id' could not be found."], 404);
        }

        try {
            $requestBody = $request->toArray();
        } catch (JsonException $e) {
            return $this->json(['status' => 'Failure', 'message' => $e->getMessage()], 400);
        }

        try {
            $userManager->updateUser($requestBody, $user);
        } catch (ValidationErrorException $e) {
            return $this->json(['status' => 'Failure', 'message' => 'Errors occurred during request validation.', 'errors' => $e->errors], 400);
        }

        return $this->json(['status' => 'Success', 'data' => $user]);
    }

    #[Route('/api/v1/users/{id}', name: 'app_users_delete', methods: ['DELETE'])]
    public function delete(string $id, UserManager $userManager): JsonResponse
    {
        if (null === $user = $userManager->findUser($id)) {
            return $this->json(['status' => 'Failure', 'message' => "A user with id '$id' could not be found."], 404);
        }

        $userManager->deleteUser($user);

        return $this->json(['status' => 'Success', 'message' => "The user with id '$id' was successfully deleted."]);
    }
}
