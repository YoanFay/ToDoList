<?php

namespace App\Tests\Unit;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskTest extends KernelTestCase
{
    public function getEntity(): Task
    {
        $container = static::getContainer();

        $user = $container->get('doctrine.orm.entity_manager')->find(User::class, 1);

        $task = new Task();
        $task->setTitle('Test Title');
        $task->setUser($user);
        $task->setContent('Test Content');
        $task->setCreatedAt(new \DateTime());

        return $task;
    }

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $task = $this->getEntity();

        $errors = $container->get('validator')->validate($task);

        $this->assertCount(0, $errors);
        $this->assertEquals('object', gettype($task->getCreatedAt()));
    }

    public function testInvalidBlank(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $task = $this->getEntity();
        $task->setTitle('');
        $task->setContent('');

        $errors = $container->get('validator')->validate($task);

        $this->assertCount(2, $errors);
    }
}
