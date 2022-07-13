<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\IndexController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IndexControllerTest extends KernelTestCase
{
    public function test_index_action_works(): void
    {
        static::bootKernel();

        /** @var IndexController $indexController */
        $indexController = static::getContainer()->get(IndexController::class);

        $jsonResponse = $indexController->index();

        self::assertSame(200, $jsonResponse->getStatusCode());
        self::assertJson((string) $jsonResponse->getContent());
        self::assertTrue($jsonResponse->headers->has('content-type'));
        self::assertSame('application/json', $jsonResponse->headers->get('content-type'));

        /** @var mixed[] $decodedContent */
        $decodedContent = json_decode((string) $jsonResponse->getContent(), true);

        self::assertIsArray($decodedContent);
        self::assertArrayHasKey('status', $decodedContent);
        self::assertArrayHasKey('message', $decodedContent);
        self::assertSame("Success", $decodedContent['status']);
        self::assertSame("It's working! 🚀", $decodedContent['message']);
    }
}
