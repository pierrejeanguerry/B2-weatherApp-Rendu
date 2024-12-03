<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthManager;
use App\Service\RequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'get_user', methods: ['GET'], priority: 2)]
    public function index(
        #[CurrentUser()] User $user,
        Request $request,
        AuthManager $auth,
    ): Response {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;
            return $this->json([
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ], 200, [], [
                'groups' => ['user']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/users', name: 'register', methods: ['POST'], priority: 2)]
    public function register(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher,
        UserRepository $repo,
        RequestValidator $validator
    ): Response {
        try {
            $manager->getConnection()->beginTransaction();

            $requiredFields = [
                'email' => 'stringNotEmpty',
                'username' => 'stringNotEmpty',
                'password' => 'stringNotEmpty'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse) {
                return $body;
            }

            $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,20}$/';
            if (!preg_match($passwordRegex, $body['password']))
                return new JsonResponse(['error' => 'Invalid password value'], 400);

            $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
            if (!preg_match($emailRegex, $body['email']))
                return new JsonResponse(['error' => 'Invalid email value'], 400);

            $existingUser = $repo->findOneBy(["email" => $body['email']]);
            if ($existingUser)
                return new JsonResponse(['error' => 'User already exist'], 409);

            $user = new User;
            $hashedPassword = $hasher->hashPassword($user, $body['password']);
            $user
                ->setEmail($body['email'])
                ->setUsername($body['username'])
                ->setRoles($user->getRoles())
                ->setPassword($hashedPassword);
            $manager->persist($user);
            $manager->flush();

            $manager->getConnection()->commit();
            return new JsonResponse(['message' => 'User created'], 201);
        } catch (\Exception $e) {
            $manager->getConnection()->rollBack();
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/api/users', name: 'update_username', methods: ['PATCH'], priority: 2)]
    public function updateUser(
        #[CurrentUser()] User $user,
        Request $request,
        EntityManagerInterface $manager,
        AuthManager $auth,
        UserPasswordHasherInterface $hasher,
        RequestValidator $validator
    ): Response {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;

            $requiredFields = [
                'new_email' => 'string',
                'new_password' => 'string',
                'new_username' => 'string',
                'password' => 'string'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse) {
                return $body;
            }

            if (empty($body["new_username"]) && !password_verify($body['password'], $user->getPassword()))
                return new JsonResponse(['error' => 'Wrong credentials'], 403);

            $manager->getConnection()->beginTransaction();

            if (!empty($body["new_password"])) {
                $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,20}$/';
                if (!preg_match($passwordRegex, $body['new_password'])) {
                    $manager->getConnection()->rollBack();
                    return new JsonResponse(['error' => 'Invalid password value'], 400);
                }
                $hashedPassword = $hasher->hashPassword($user, $body['new_password']);
                $user->setPassword($hashedPassword);
            }

            if (!empty($body["new_email"])) {
                $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
                if (!preg_match($emailRegex, $body['new_email'])) {
                    $manager->getConnection()->rollBack();
                    return new JsonResponse(['error' => 'Invalid email value'], 400);
                }
                $user->setEmail($body['new_email']);
            }

            if (!empty($body['new_username'])) {
                $user->setUsername($body['new_username']);
            }

            $manager->persist($user);
            $manager->flush();

            $manager->getConnection()->commit();

            return new JsonResponse(['message' => 'User updated'], 200);
        } catch (\Exception $e) {
            $manager->getConnection()->rollBack();

            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/users', name: 'delete_user', methods: ['DELETE'], priority: 2)]
    public function deleteId(
        #[CurrentUser()] User $user,
        Request $request,
        EntityManagerInterface $manager,
        AuthManager $auth,
        RequestValidator $validator
    ): Response {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;

            $requiredFields = [
                'password' => 'stringNotEmpty'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse) {
                return $body;
            }

            if (!password_verify($body['password'], $user->getPassword()))
                return new JsonResponse(['error' => 'Wrong credentials'], 403);

            $manager->getConnection()->beginTransaction();

            $manager->remove($user);
            $manager->flush();

            $manager->getConnection()->commit();
            $manager->clear();

            $session = $request->getSession();
            $session->invalidate();

            return new JsonResponse(['message' => 'User deleted'], 200);
        } catch (\Exception $e) {
            $manager->getConnection()->rollBack();

            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
