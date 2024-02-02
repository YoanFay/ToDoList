<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks", name="task_list")
     */
    public function listAction(TaskRepository $taskRepository)
    {

        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findBy(['user' => $this->getUser()])]);
    }


    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function createAction(Request $request, TaskRepository $taskRepository, EntityManagerInterface $entityManager)
    {

        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($this->getUser());

            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }


    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function editAction(Task $task, Request $request, EntityManagerInterface $entityManager)
    {

        if ($task->getUser() !== $this->getUser()) {

            $this->addFlash('error', "Cette tâche n'est pas disponible.");

            return $this->redirectToRoute('homepage');
        }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }


    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTaskAction($id, EntityManagerInterface $entityManager)
    {

        $task = $entityManager->getRepository(Task::class)->find($id);

        if ($task->getUser() !== $this->getUser()) {

            $this->addFlash('error', "Cette tâche n'est pas disponible.");

            return $this->redirectToRoute('homepage');
        }

        $task->toggle(!$task->isDone());
        $entityManager->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }


    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction(TaskRepository $taskRepository, EntityManagerInterface $entityManager, $id)
    {

        $task = $taskRepository->find($id);

        if ($this->getUser()->getRole() !== 'ROLE_ADMIN') {
            if ($task->getUser() !== $this->getUser()) {

                $this->addFlash('error', "Cette tâche n'est pas disponible.");

                return $this->redirectToRoute('homepage');
            }
        }

        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
