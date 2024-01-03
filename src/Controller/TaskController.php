<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\Task;
use App\Form\TaskType;
use App\Form\TaskUpdateType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Config\Doctrine\Orm\EntityManagerConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

#[Route('/task')]
class TaskController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'app_task')]
    public function index(Request $request, TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $task
                ->setStatus(Status::New)
                ->setCreatedAt(new \DateTimeImmutable());

            $this->em->persist($task);
            $this->em->flush();

            $this->addFlash('notice', 'Successfully added');

            return $this->redirect('task');
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $tasks = $taskRepository->getTaskPaginator($offset);
        return $this->render('task/index.html.twig', [
            'task_form' => $form,
            'tasks' => $tasks,
            'previous' => $offset - TaskRepository::TASKS_PRE_PAGE,
            'next' => min(count($tasks), $offset + TaskRepository::TASKS_PRE_PAGE)
        ]);
    }

    #[Route('/{id}', name: 'app_task_show')]
    public function show(Task $task, Request $request): Response
    {
        $form = $this->createForm(TaskUpdateType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->flush();
            $this->addFlash('notice', 'Successfully updated');

            return $this->redirect('/task');
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
            'task_form' => $form
        ]);
    }
}
