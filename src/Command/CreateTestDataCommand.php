<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-data',
    description: 'Fills the users.json file and the database with test data.'
)]
class CreateTestDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = [
            ['firstName' => 'John', 'lastName' => 'Doe', 'email' => 'john.doe@example.com'],
            ['firstName' => 'Max', 'lastName' => 'Mustermann', 'email' => 'max.mustermann@example.com'],
            ['firstName' => 'Biene', 'lastName' => 'Maja', 'email' => 'biene.maja@example.com'],
        ];

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $userRepository->deleteAllUsers();
        $io->success('All existing database users have been successfully deleted.');

        $jsonUsers = [];
        foreach ($users as $userData) {
            $user = new User();
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setEmail($userData['email']);
            $user->setPassword(password_hash('Password123', PASSWORD_DEFAULT));

            $jsonUsers[] = $user->toArray();
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
        $io->success('New database users have been successfully added.');

        $jsonFile = __DIR__ . '/../../var/users.json';
        if (file_exists($jsonFile)) {
            if (unlink($jsonFile) === false) {
                $io->error('The old users.json file could not be successfully deleted.');

                return Command::FAILURE;
            }

            $io->success('The old users.json file has been successfully deleted.');
        }

        if (file_put_contents($jsonFile, json_encode($jsonUsers)) === false) {
            $io->error('The new json users could not be successfully written to the users.json file.');

            return Command::FAILURE;
        }

        $io->success('New json users have been successfully added.');

        return Command::SUCCESS;
    }
}
