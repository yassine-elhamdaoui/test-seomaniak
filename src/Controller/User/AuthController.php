<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $fullName = $request->request->get('fullName');
        if ($this->userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse(['code'=> JsonResponse::HTTP_CONFLICT,'error' => 'Email already exists'], JsonResponse::HTTP_CONFLICT);
        }
        $user = new User();
        $imageFile = $request->files->get('imageFile');
        if ($imageFile) {
            $user->setImageFile($imageFile);
        }
        $user->setEmail($email);
        $user->setFullName($fullName);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new JsonResponse(['code'=>JsonResponse::HTTP_CREATED,'message'=>'User created successfully'],JsonResponse::HTTP_CREATED);
    }

}
