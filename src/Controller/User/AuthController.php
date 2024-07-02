<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

final class AuthController
{
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository , EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/register', name: 'register', methods: ['POST'])]
    function addUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $email = $data['email'];
        if ($this->userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse(['code'=> JsonResponse::HTTP_CONFLICT,'error' => 'Email already exists'], JsonResponse::HTTP_CONFLICT);
        }
        $user->setEmail($email);
        $user->setFullName($data['fullName']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new JsonResponse(['code'=>JsonResponse::HTTP_CREATED,'message'=>'User created successfully'],JsonResponse::HTTP_CREATED);
    }
}
