<?php

declare(strict_types=1);

namespace App\Tests;

use App\Adapter\UserAdapterInterface;
use App\Entity\User;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractUserControllerTestCase extends KernelTestCase
{
    private string $projectDirectory;

    public function setUp(): void
    {
        static::bootKernel();

        /** @var ContainerBagInterface $containerBag */
        $containerBag = static::getContainer()->get(ContainerBagInterface::class);

        /** @var string $projectDirectory */
        $projectDirectory = $containerBag->get('kernel.project_dir');

        $this->projectDirectory = $projectDirectory;

        // Precautionary remove the old data_test.db file if present and create a new database and load migrations.
        $usersTestSqliteFile = $this->projectDirectory . '/var/data_test.db';
        if (file_exists($usersTestSqliteFile)) {
            if (false === unlink($usersTestSqliteFile)) {
                throw new RuntimeException("Can not remove '$usersTestSqliteFile'.");
            }
        }

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(['command' => 'doctrine:migrations:migrate', '--no-interaction' => true]);
        $application->run($input, new NullOutput());

        // Precautionary remove the old users_test.json file if present and create a new users_test.json file.
        $usersTestJsonFile = $this->projectDirectory . '/var/users_test.json';
        if (file_exists($usersTestJsonFile)) {
            if (false === unlink($usersTestJsonFile)) {
                throw new RuntimeException("Can not remove '$usersTestJsonFile'.");
            }
        }

        if (false === file_put_contents($projectDirectory . '/var/users_test.json', json_encode([]))) {
            throw new RuntimeException("Can not write to '$projectDirectory/var/users_test.json'.");
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Remove the old data_test.db file.
        $usersTestDatabaseFile = $this->projectDirectory . '/var/data_test.db';
        if (file_exists($usersTestDatabaseFile)) {
            if (false === unlink($usersTestDatabaseFile)) {
                throw new RuntimeException("Can not remove '$usersTestDatabaseFile'.");
            }
        }

        // Remove the old users_test.json file
        $usersTestJsonFile = $this->projectDirectory . '/var/users_test.json';
        if (file_exists($usersTestJsonFile)) {
            if (false === unlink($usersTestJsonFile)) {
                throw new RuntimeException("Can not remove '$usersTestJsonFile'.");
            }
        }
    }

    /**
     * @return User[]
     * @throws RuntimeException
     */
    protected function createTestUsers(): array
    {
        $usersData = [
            ['firstName' => 'John', 'lastName' => 'Doe', 'email' => 'john.doe@example.com'],
            ['firstName' => 'Max', 'lastName' => 'Mustermann', 'email' => 'max.mustermann@example.com'],
            ['firstName' => 'Biene', 'lastName' => 'Maja', 'email' => 'biene.maja@example.com'],
        ];

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $users = [];
        $jsonUsers = [];
        foreach ($usersData as $userData) {
            $user = new User();
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setEmail($userData['email']);
            $user->setPassword(password_hash('Password123', PASSWORD_DEFAULT));

            $users[] = $user;
            $jsonUsers[] = $user->toArray();

            $entityManager->persist($user);
        }

        $entityManager->flush();

        $usersTestJsonFile = $this->projectDirectory . '/var/users_test.json';
        if (!file_exists($usersTestJsonFile)) {
            throw new RuntimeException("The '$usersTestJsonFile' file should have been created in the setUp method.");
        }

        if (false === file_put_contents($usersTestJsonFile, json_encode($jsonUsers))) {
            throw new RuntimeException("Can not write to '$usersTestJsonFile' file.");
        }

        return $users;
    }

    protected function getUserManager(string $adapterClass): UserManager
    {
        /** @var UserAdapterInterface $adapter */
        $adapter = static::getContainer()->get($adapterClass);
        static::getContainer()->set(UserAdapterInterface::class, $adapter);

        /** @var UserManager $userManager */
        $userManager = static::getContainer()->get(UserManager::class);

        return $userManager;
    }

    protected function testStatusCodeAndJsonContentAndContentType(JsonResponse $jsonResponse, int $expectedStatusCode): void
    {
        self::assertSame($expectedStatusCode, $jsonResponse->getStatusCode());
        self::assertJson((string) $jsonResponse->getContent());
        self::assertTrue($jsonResponse->headers->has('content-type'));
        self::assertSame('application/json', $jsonResponse->headers->get('content-type'));
    }

    /**
     * @param mixed[]|null $expectedData
     */
    protected function testResponseContent(JsonResponse $jsonResponse, string $expectedStatusMessage, ?string $expectedMessage = null, ?array $expectedData = null): void
    {
        /** @var mixed[] $decodedContent */
        $decodedContent = json_decode((string) $jsonResponse->getContent(), true);

        self::assertArrayHasKey('status', $decodedContent);
        self::assertSame($expectedStatusMessage, $decodedContent['status']);

        if ($expectedMessage !== null) {
            self::assertArrayHasKey('message', $decodedContent);
            self::assertSame($expectedMessage, $decodedContent['message']);
        }

        if ($expectedData !== null) {
            self::assertArrayHasKey('data', $decodedContent);
            self::assertSame($expectedData, $decodedContent['data']);
        }
    }
}
