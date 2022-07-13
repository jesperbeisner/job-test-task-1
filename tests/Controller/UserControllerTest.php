<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Adapter\DatabaseUserAdapter;
use App\Adapter\JsonUserAdapter;
use App\Adapter\UserAdapterInterface;
use App\Controller\UserController;
use App\Entity\User;
use App\Tests\AbstractUserControllerTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * So much code duplication... But I had to test both adapters. It is what it is. `¯\_(ツ)_/¯`
 */
class UserControllerTest extends AbstractUserControllerTestCase
{
    public function test_index_will_return_no_users_when_no_id_is_specified_and_database_has_no_users(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $jsonResponse = $userController->index('', $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success', expectedData: []);
    }

    public function test_index_will_return_no_users_when_no_id_is_specified_and_json_file_has_no_users(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $jsonResponse = $userController->index('', $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success', expectedData: []);
    }

    public function test_index_will_return_all_users_when_no_id_is_specified_and_database_has_users(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $jsonResponse = $userController->index('', $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);

        /** @var string $jsonUsers */
        $jsonUsers = json_encode($users);

        /** @var mixed[] $decodedUsers */
        $decodedUsers = json_decode($jsonUsers, true);

        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success', expectedData: $decodedUsers);
    }

    public function test_index_will_return_all_users_when_no_id_is_specified_and_json_file_has_users(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $jsonResponse = $userController->index('', $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);

        /** @var string $jsonUsers */
        $jsonUsers = json_encode($users);

        /** @var mixed[] $decodedUsers */
        $decodedUsers = json_decode($jsonUsers, true);

        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success', expectedData: $decodedUsers);
    }

    public function test_index_will_return_one_user_when_id_is_specified_and_database_has_user(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $jsonResponse = $userController->index($users[0]->getId(), $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);

        /** @var string $jsonUser */
        $jsonUser = json_encode($users[0]);

        /** @var mixed[] $decodedUser */
        $decodedUser = json_decode($jsonUser, true);

        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success', expectedData: $decodedUser);
    }

    public function test_index_will_return_one_user_when_id_is_specified_and_json_file_has_user(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $jsonResponse = $userController->index($users[0]->getId(), $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);

        /** @var string $jsonUser */
        $jsonUser = json_encode($users[0]);

        /** @var mixed[] $decodedUser */
        $decodedUser = json_decode($jsonUser, true);

        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success', expectedData: $decodedUser);
    }

    public function test_index_will_return_no_user_when_id_is_specified_and_database_has_no_user(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $jsonResponse = $userController->index('Test', $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 404);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: "A user with id 'Test' could not be found.");
    }

    public function test_index_will_return_no_user_when_id_is_specified_and_json_file_has_no_user(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $jsonResponse = $userController->index('Test', $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 404);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: "A user with id 'Test' could not be found.");
    }

    public function test_create_will_create_and_return_database_user_when_post_data_has_no_errors(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->create($request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 201);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('data', $decodedBody);

        /** @var array<string, string|null> $userData */
        $userData = $decodedBody['data'];

        self::assertArrayHasKey('id', $userData);
        self::assertArrayHasKey('firstName', $userData);
        self::assertArrayHasKey('lastName', $userData);
        self::assertArrayHasKey('email', $userData);
        self::assertArrayHasKey('created', $userData);
        self::assertArrayHasKey('updated', $userData);

        /** @var UserAdapterInterface $userAdapter */
        $userAdapter = static::getContainer()->get(DatabaseUserAdapter::class);

        $user = $userAdapter->findUser((string) $userData['id']);

        self::assertNotNull($user);
        self::assertInstanceOf(User::class, $user);

        /** @var User $realUserEntity */
        $realUserEntity = $user;

        self::assertSame('John', $realUserEntity->getFirstName());
        self::assertSame('Doe', $realUserEntity->getLastName());
        self::assertSame('john.doe@example.com', $realUserEntity->getEmail());
    }

    public function test_create_will_create_and_return_json_file_user_when_post_data_has_no_errors(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->create($request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 201);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('data', $decodedBody);

        /** @var array<string, string|null> $userData */
        $userData = $decodedBody['data'];

        self::assertArrayHasKey('id', $userData);
        self::assertArrayHasKey('firstName', $userData);
        self::assertArrayHasKey('lastName', $userData);
        self::assertArrayHasKey('email', $userData);
        self::assertArrayHasKey('created', $userData);
        self::assertArrayHasKey('updated', $userData);

        /** @var UserAdapterInterface $userAdapter */
        $userAdapter = static::getContainer()->get(JsonUserAdapter::class);

        $user = $userAdapter->findUser((string) $userData['id']);

        self::assertNotNull($user);
        self::assertInstanceOf(User::class, $user);

        /** @var User $realUserEntity */
        $realUserEntity = $user;

        self::assertSame('John', $realUserEntity->getFirstName());
        self::assertSame('Doe', $realUserEntity->getLastName());
        self::assertSame('john.doe@example.com', $realUserEntity->getEmail());
    }

    public function test_create_will_return_error_when_post_data_is_not_valid(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $request = new Request(content: 'Test');

        $jsonResponse = $userController->create($request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Could not decode request body.');
    }

    public function test_create_will_return_error_when_post_data_values_are_not_valid(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'A',
            'lastName' => 'B',
            'email' => 'NoValidMail',
            'password' => 'TooShort',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->create($request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(4, count($errorsArray));

        /** @var mixed[] $errorArray */
        foreach ($errorsArray as $errorArray) {
            self::assertArrayHasKey('errorMessage', $errorArray);

            $possibleErrorMessages = [
                "The 'firstName' field value is too short. It should have 2 characters or more.",
                "The 'lastName' field value is too short. It should have 2 characters or more.",
                "The 'email' field value is not a valid email address.",
                "The 'password' field value is too short. It should have 10 characters or more.",
            ];

            self::assertTrue(in_array($errorArray['errorMessage'], $possibleErrorMessages, true));
        }
    }

    public function test_create_will_return_error_when_post_data_is_not_complete(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->create($request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(1, count($errorsArray));

        /** @var mixed[] $errorArray */
        $errorArray = $errorsArray[0];

        self::assertArrayHasKey('errorMessage', $errorArray);
        self::assertSame("The field 'password' is missing.", $errorArray['errorMessage']);
    }

    public function test_create_will_return_error_when_post_data_has_too_many_values(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123',
            'test' => 'test',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->create($request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(1, count($errorsArray));

        /** @var mixed[] $errorArray */
        $errorArray = $errorsArray[0];

        self::assertArrayHasKey('errorMessage', $errorArray);
        self::assertSame("The field 'test' was not expected.", $errorArray['errorMessage']);
    }

    public function test_create_will_return_error_when_database_user_email_is_already_used(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => $users[0]->getEmail(),
            'password' => 'Password123',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->create($request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(1, count($errorsArray));

        /** @var mixed[] $errorArray */
        $errorArray = $errorsArray[0];

        self::assertArrayHasKey('errorMessage', $errorArray);
        self::assertSame("A user with email address '{$users[0]->getEmail()}' already exists.", $errorArray['errorMessage']);
    }

    public function test_create_will_return_error_when_json_file_user_email_is_already_used(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => $users[0]->getEmail(),
            'password' => 'Password123',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->create($request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(1, count($errorsArray));

        /** @var mixed[] $errorArray */
        $errorArray = $errorsArray[0];

        self::assertArrayHasKey('errorMessage', $errorArray);
        self::assertSame("A user with email address '{$users[0]->getEmail()}' already exists.", $errorArray['errorMessage']);
    }

    public function test_update_will_return_failure_when_database_user_is_not_found(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $jsonResponse = $userController->update('Test', new Request(), $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 404);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: "A user with id 'Test' could not be found.");
    }

    public function test_update_will_return_failure_when_json_file_user_is_not_found(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $jsonResponse = $userController->update('Test', new Request(), $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 404);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: "A user with id 'Test' could not be found.");
    }

    public function test_update_will_return_error_when_post_data_is_not_valid(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $request = new Request(content: 'Test');

        $jsonResponse = $userController->update($users[0]->getId(), $request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Could not decode request body.');
    }

    public function test_update_will_return_error_when_post_data_values_are_not_valid(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'A',
            'lastName' => 'B',
            'email' => 'NoValidMail',
            'password' => 'TooShort',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->update($users[0]->getId(), $request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(4, count($errorsArray));

        /** @var mixed[] $errorArray */
        foreach ($errorsArray as $errorArray) {
            self::assertArrayHasKey('errorMessage', $errorArray);

            $possibleErrorMessages = [
                "The 'firstName' field value is too short. It should have 2 characters or more.",
                "The 'lastName' field value is too short. It should have 2 characters or more.",
                "The 'email' field value is not a valid email address.",
                "The 'password' field value is too short. It should have 10 characters or more.",
            ];

            self::assertTrue(in_array($errorArray['errorMessage'], $possibleErrorMessages, true));
        }
    }

    public function test_update_will_return_error_when_post_data_is_not_complete(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->update($users[0]->getId(), $request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(1, count($errorsArray));

        /** @var mixed[] $errorArray */
        $errorArray = $errorsArray[0];

        self::assertArrayHasKey('errorMessage', $errorArray);
        self::assertSame("The field 'password' is missing.", $errorArray['errorMessage']);
    }

    public function test_update_will_return_error_when_post_data_has_too_many_values(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123',
            'test' => 'test',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->update($users[0]->getId(), $request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(1, count($errorsArray));

        /** @var mixed[] $errorArray */
        $errorArray = $errorsArray[0];

        self::assertArrayHasKey('errorMessage', $errorArray);
        self::assertSame("The field 'test' was not expected.", $errorArray['errorMessage']);
    }

    public function test_update_will_return_error_when_database_user_email_is_already_used(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => $users[1]->getEmail(),
            'password' => 'Password123',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->update($users[0]->getId(), $request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(1, count($errorsArray));

        /** @var mixed[] $errorArray */
        $errorArray = $errorsArray[0];

        self::assertArrayHasKey('errorMessage', $errorArray);
        self::assertSame("A user with email address '{$users[1]->getEmail()}' already exists.", $errorArray['errorMessage']);
    }

    public function test_update_will_return_error_when_json_file_user_email_is_already_used(): void
    {
        $users = $this->createTestUsers();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $content = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => $users[1]->getEmail(),
            'password' => 'Password123',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->update($users[0]->getId(), $request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 400);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: 'Errors occurred during request validation.');

        /** @var mixed[] $decodedBody */
        $decodedBody = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('errors', $decodedBody);
        self::assertIsArray($decodedBody['errors']);

        /** @var mixed[] $errorsArray */
        $errorsArray = $decodedBody['errors'];

        self::assertSame(1, count($errorsArray));

        /** @var mixed[] $errorArray */
        $errorArray = $errorsArray[0];

        self::assertArrayHasKey('errorMessage', $errorArray);
        self::assertSame("A user with email address '{$users[1]->getEmail()}' already exists.", $errorArray['errorMessage']);
    }

    public function test_update_will_return_updated_database_user_when_post_data_was_valid(): void
    {
        $users = $this->createTestUsers();
        $oldUser = clone $users[0];
        $userId = $users[0]->getId();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $content = [
            'firstName' => 'new-' . $oldUser->getFirstName(),
            'lastName' => 'new-' . $oldUser->getLastName(),
            'email' => 'new-' . $oldUser->getEmail(),
            'password' => 'new-Password123',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->update($userId, $request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success');

        /** @var UserAdapterInterface $userAdapter */
        $userAdapter = static::getContainer()->get(UserAdapterInterface::class);

        /** @var User $newUser */
        $newUser = $userAdapter->findUser($userId);

        self::assertNotSame($oldUser->getFirstName(), $newUser->getFirstName());
        self::assertNotSame($oldUser->getLastName(), $newUser->getLastName());
        self::assertNotSame($oldUser->getEmail(), $newUser->getEmail());
        self::assertNotSame($oldUser->getPassword(), $newUser->getPassword());

        self::assertSame('new-' . $oldUser->getFirstName(), $newUser->getFirstName());
        self::assertSame('new-' . $oldUser->getLastName(), $newUser->getLastName());
        self::assertSame('new-' . $oldUser->getEmail(), $newUser->getEmail());
    }

    public function test_update_will_return_updated_json_file_user_when_post_data_was_valid(): void
    {
        $users = $this->createTestUsers();
        $oldUser = clone $users[0];
        $userId = $users[0]->getId();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $content = [
            'firstName' => 'new-' . $oldUser->getFirstName(),
            'lastName' => 'new-' . $oldUser->getLastName(),
            'email' => 'new-' . $oldUser->getEmail(),
            'password' => 'new-Password123',
        ];

        /** @var string $jsonContent */
        $jsonContent = json_encode($content);

        $request = new Request(content: $jsonContent);

        $jsonResponse = $userController->update($userId, $request, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success');

        /** @var UserAdapterInterface $userAdapter */
        $userAdapter = static::getContainer()->get(UserAdapterInterface::class);

        /** @var User $newUser */
        $newUser = $userAdapter->findUser($userId);

        self::assertNotSame($oldUser->getFirstName(), $newUser->getFirstName());
        self::assertNotSame($oldUser->getLastName(), $newUser->getLastName());
        self::assertNotSame($oldUser->getEmail(), $newUser->getEmail());
        self::assertNotSame($oldUser->getPassword(), $newUser->getPassword());

        self::assertSame('new-' . $oldUser->getFirstName(), $newUser->getFirstName());
        self::assertSame('new-' . $oldUser->getLastName(), $newUser->getLastName());
        self::assertSame('new-' . $oldUser->getEmail(), $newUser->getEmail());
    }

    public function test_delete_will_return_failure_when_database_user_is_not_found(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $jsonResponse = $userController->delete('Test', $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 404);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: "A user with id 'Test' could not be found.");
    }

    public function test_delete_will_return_failure_when_json_file_user_is_not_found(): void
    {
        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $jsonResponse = $userController->delete('Test', $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 404);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Failure', expectedMessage: "A user with id 'Test' could not be found.");
    }

    public function test_delete_will_return_success_when_database_user_is_found_and_deleted(): void
    {
        $users = $this->createTestUsers();
        $userId = $users[0]->getId();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . DatabaseUserAdapter::class);

        $user = $userManager->findUser($userId);
        self::assertNotNull($user);
        self::assertInstanceOf(User::class, $user);

        $foundUsers = $userManager->findUsers();
        self::assertSame(3, count($foundUsers));

        $jsonResponse = $userController->delete($userId, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success', expectedMessage: "The user with id '$userId' was successfully deleted.");

        $user = $userManager->findUser($userId);
        self::assertNull($user);

        $foundUsers = $userManager->findUsers();
        self::assertSame(2, count($foundUsers));
    }

    public function test_delete_will_return_success_when_json_file_user_is_found_and_deleted(): void
    {
        $users = $this->createTestUsers();
        $userId = $users[0]->getId();

        /** @var UserController $userController */
        $userController = static::getContainer()->get(UserController::class);
        $userManager = $this->getUserManager('test.' . JsonUserAdapter::class);

        $user = $userManager->findUser($userId);
        self::assertNotNull($user);
        self::assertInstanceOf(User::class, $user);

        $foundUsers = $userManager->findUsers();
        self::assertSame(3, count($foundUsers));

        $jsonResponse = $userController->delete($userId, $userManager);

        $this->testStatusCodeAndJsonContentAndContentType($jsonResponse, 200);
        $this->testResponseContent($jsonResponse, expectedStatusMessage: 'Success', expectedMessage: "The user with id '$userId' was successfully deleted.");

        $user = $userManager->findUser($userId);
        self::assertNull($user);

        $foundUsers = $userManager->findUsers();
        self::assertSame(2, count($foundUsers));
    }
}
