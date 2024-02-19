<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function testUserCreate(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        $crawler = $client->request('GET', $urlGenerator->generate('user_create'));

        $form = $crawler->filter('form[name=user]')->form([
            'user[username]' => "Test",
            'user[password][first]' => "motDePasseTest",
            'user[password][second]' => "motDePasseTest",
            'user[email]' => "test.email@email.com",
            'user[role]' => "ROLE_USER"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', "L'utilisateur a bien été ajouté.");
    }


    public function testUserList(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 3);

        $client->loginUser($user);

        $client->request('GET', $urlGenerator->generate('user_list'));

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', "Liste des utilisateurs");
    }


    public function testUserListErrorNotAdmin(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $client->request('GET', $urlGenerator->generate('user_list'));

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-danger', "L'accès à cette page ne vous est pas autorisé.");
    }


    public function testUserListErrorNotLogin(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        $client->request('GET', $urlGenerator->generate('user_list'));

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-danger', "L'accès à cette page ne vous est pas autorisé.");
    }


    public function testUpdateUser(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 3);

        $client->loginUser($user);

        $crawler = $client->request('GET', $urlGenerator->generate('user_edit', ['id' => 4]));

        $form = $crawler->filter('form[name=user]')->form([
            'user[username]' => "TestUpdate",
            'user[password][first]' => "motDePasseTestUpdate",
            'user[password][second]' => "motDePasseTestUpdate",
            'user[email]' => "testupdate.email@email.com",
            'user[role]' => "ROLE_USER"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', "L'utilisateur a bien été modifié");
    }
}
