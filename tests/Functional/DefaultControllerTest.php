<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testHomepage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $newTask = $crawler->selectLink('Créer une nouvelle tâche');

        $this->assertEquals(1, count($newTask));

        $viewTask = $crawler->selectLink('Consulter la liste des tâches à faire');

        $this->assertEquals(1, count($viewTask));

        $viewEndTask = $crawler->selectLink('Consulter la liste des tâches terminées');

        $this->assertEquals(1, count($viewEndTask));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
    }
}
