<?php

namespace App\Tests\Functional;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    public function testAddTaskWithoutLogin(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        $client->request('GET', $urlGenerator->generate('task_create'));

        $client->followRedirect();

        $this->assertSelectorTextContains('button.btn.btn-success', 'Se connecter');
    }

    public function testAddTaskWithLogin(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $crawler = $client->request('GET', $urlGenerator->generate('task_create'));

        $form = $crawler->filter('form[name=task]')->form([
            'task[title]' => "Titre de la tâche",
            'task[content]' => "Contenue de la tâche"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'La tâche a été bien été ajoutée.');
    }


    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws NotSupported
     */
    public function testUpdateTask(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneBy(['user' => $user]);

        $crawler = $client->request('GET', $urlGenerator->generate('task_edit', ['id' => $task->getId()]));

        $form = $crawler->filter('form[name=task]')->form([
            'task[title]' => "Nouveau titre de la tâche",
            'task[content]' => "Nouveau contenue de la tâche"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'La tâche a bien été modifiée.');
    }


    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws NotSupported
     */
    public function testUpdateErrorWrongUser(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 2);

        $client->loginUser($user);

        $otherUser = $entityManager->find(User::class, 1);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneBy(['user' => $otherUser]);

        $client->request('GET', $urlGenerator->generate('task_edit', ['id' => $task->getId()]));

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-danger', "Cette tâche n'est pas disponible.");
    }


    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws NotSupported
     */
    public function testToggleTask(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneBy(['user' => $user]);

        $client->request('GET', $urlGenerator->generate('task_toggle', ['id' => $task->getId()]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'marquée comme faite.');

        $client->request('GET', $urlGenerator->generate('task_toggle', ['id' => $task->getId()]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'marquée comme non faite.');
    }


    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws NotSupported
     */
    public function testToggleErrorWrongUser(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 2);

        $client->loginUser($user);

        $otherUser = $entityManager->find(User::class, 1);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneBy(['user' => $otherUser]);

        $client->request('GET', $urlGenerator->generate('task_toggle', ['id' => $task->getId()]));

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-danger', "Cette tâche n'est pas disponible.");
    }


    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws NotSupported
     */
    public function testToggleErrorNoTask(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $client->request('GET', $urlGenerator->generate('task_toggle', ['id' => 404]));

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-danger', "Cette tâche n'est pas disponible.");
    }


    /**
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ORMException
     */
    public function testTaskList(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $client->request('GET', $urlGenerator->generate('task_list'));

        $this->assertResponseIsSuccessful();

        $this->assertSelectorNotExists('div.alert.alert-warning', "Il n'y a pas encore de tâche enregistrée.");
    }


    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws NotSupported
     */
    public function testDeleteErrorWrongUser(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 2);

        $client->loginUser($user);

        $otherUser = $entityManager->find(User::class, 1);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneBy(['user' => $otherUser]);

        $client->request('GET', $urlGenerator->generate('task_delete', ['id' => $task->getId()]));

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-danger', "Cette tâche n'est pas disponible.");
    }


    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws NotSupported
     */
    public function testDeleteErrorNoTask(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $client->request('GET', $urlGenerator->generate('task_delete', ['id' => 404]));

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-danger', "Cette tâche n'est pas disponible.");
    }


    /**
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ORMException
     */
    public function testTaskDelete(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');

        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneBy(['user' => $user]);

        $client->request('GET', $urlGenerator->generate('task_delete', ['id' => $task->getId()]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', "La tâche a bien été supprimée.");
    }
}
