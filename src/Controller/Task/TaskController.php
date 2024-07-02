<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class TaskController 
{
    private TaskRepository $taskRepository;
    private EntityManagerInterface $entityManager;
    private Security $security;
    private SerializerInterface $serializer;

    public function __construct(TaskRepository $taskRepository, EntityManagerInterface $entityManager, Security $security, SerializerInterface $serializer)
    {  
             $this->taskRepository = $taskRepository;
             $this->entityManager = $entityManager;
             $this->security = $security;
             $this->serializer = $serializer;

    }

    #[Route('/api/tasks', name: 'task_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $developer = $this->security->getUser();
        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description']);
        $task->setPriority($data['priority']);
        $task->setStatus($data['status']);
        $task->setDeveloper($developer);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return new JsonResponse(['code'=>JsonResponse::HTTP_CREATED,'message'=>'Task created successfully'],JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/tasks', name: 'task_getAll', methods: ['GET'])]
    public function getAll(): JsonResponse
{
    $tasks = $this->taskRepository->findAll();
    $data = $this->serializer->serialize($tasks, 'json', ['groups' => 'task:read']);
    
    return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
}


    

    #[Route('/api/tasks/{id}', name: 'task_getById', methods: ['GET'])]
    public function getById(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);
         if(!$task)
         { 
            return new JsonResponse(['code'=> JsonResponse::HTTP_NOT_FOUND,'error' => 'Task Not exists'], JsonResponse::HTTP_NOT_FOUND);
         }
         $data = $this->serializer->serialize($task, 'json', ['groups' => 'task:read']);
        return new JsonResponse($data,JsonResponse::HTTP_OK,[],true);
    }

    #[Route('/api/tasks/{id}', name: 'task_update', methods: ['PUT'])]
    public function update(Request $request, int $id ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $task = $this->taskRepository->find($id);
         if(!$task)
         { 
            return new JsonResponse(['code'=> JsonResponse::HTTP_NOT_FOUND,'error' => 'Task Not exists'], JsonResponse::HTTP_NOT_FOUND);
         }
        
         if (empty($data['title'])==false) {
            $task->setTitle($data['title']);
         }
         if (empty($data['description'])==false) {
            $task->setDescription($data['description']);
         }
         if (empty($data['priority'])==false) {
            $task->setPriority($data['priority']);
         } 
         if (empty($data['status'])==false) {
            $task->setStatus($data['status']);
         }
        

        $this->entityManager->flush();

        return new JsonResponse(['code'=>JsonResponse::HTTP_OK,'message'=>'Task Updated successfully'],JsonResponse::HTTP_OK);
    }

    #[Route('/api/tasks/{id}', name: 'Remove', methods: ['DELETE'])]
    public function Remove(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);
         if(!$task)
         { 
            return new JsonResponse(['code'=> JsonResponse::HTTP_NOT_FOUND,'error' => 'Task Not exists'], JsonResponse::HTTP_NOT_FOUND);
         }
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return new JsonResponse(['code'=> JsonResponse::HTTP_OK,'error' => 'Task Has been Removed'], JsonResponse::HTTP_OK);
    }
}
