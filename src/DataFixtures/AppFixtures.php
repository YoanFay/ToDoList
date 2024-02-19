<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'username' => 'Anonyme',
                'password' => 'motDePasseAnonyme',
                'email' => 'anonyme@email.com',
                'role' => 'ROLE_USER'
            ],
            [
                'username' => 'Default',
                'password' => 'motDePasseDefault',
                'email' => 'default@email.com',
                'role' => 'ROLE_USER'
            ],
            [
                'username' => 'Admin',
                'password' => 'motDePasseAdmin',
                'email' => 'admin@email.com',
                'role' => 'ROLE_ADMIN'
            ],
        ];

        foreach ($users as $userData) {
            $user = new User();

            $user->setUsername($userData['username']);
            $user->setRole($userData['role']);
            $user->setEmail($userData['email']);

            $password = password_hash($userData['password'], PASSWORD_BCRYPT);
            $user->setPassword($password);

            $manager->persist($user);

            $manager->flush();
        }
    }
}
