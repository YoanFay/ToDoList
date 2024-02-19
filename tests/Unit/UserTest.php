<?php

namespace App\Tests\Unit;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function getEntity(): User
    {
        $user = new User();
        $user->setEmail('email@email.com');
        $user->setUsername('testUsername');
        $user->setPassword('testPassword');
        $user->setRole('ROLE_USER');

        return $user;
    }

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = $this->getEntity();

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(0, $errors);
        $this->assertEquals(null, $user->getSalt());
    }

    public function testInvalidBlank(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = $this->getEntity();
        $user->setEmail('');
        $user->setUsername('');
        $user->setPassword('');
        $user->setRole('');

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(4, $errors);
    }

    public function testInvalidLengthAndMailFormat(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = $this->getEntity();
        $longString = "Je suis actuellement en train de faire un test pour savoir si cette chaine de caractère va être détecter comme trop longue";
        $user->setEmail($longString);
        $user->setUsername($longString);
        $user->setPassword($longString);
        $user->setRole($longString);

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(5, $errors);
    }

    /*public function testInvalidContent(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = $this->getEntity();
        $user->setContent('');

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(1, $errors);
    }*/
}
