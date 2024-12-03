<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AuthManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LoginController extends AbstractController
{
  #[Route('/api/login', name: 'api_login', methods: ['POST'], priority: 2)]
  public function index(#[CurrentUser()] ?User $user, Request $request): Response
  {
    if (null === $user) {
      return $this->json([
        'message' => 'missing credentials',
      ], Response::HTTP_UNAUTHORIZED);
    }
    $token = bin2hex(random_bytes(32));
    $session = $request->getSession();
    $session->set('token_user', $token);
    $username = $user->getUsername();
    $id = $user->getId();
    return $this->json([
      'token_user' => $token,
      'username' => $username,
      'id' => $id
    ]);
  }

  #[Route('/api/login/check', name: 'check_login', methods: ['POST'], priority: 2)]
  public function check(#[CurrentUser()] ?User $user, Request $request, AuthManager $auth): Response
  {
    if (($authResponse = $auth->checkAuth($user, $request)) !== null)
      return $authResponse;

    return new JsonResponse(['message' => 'Credentials are valid'], 200);
  }

  #[Route('/api/login/logout', name: 'logout_login', methods: ['POST'], priority: 2)]
  public function logout(#[CurrentUser()] ?User $user, Request $request, AuthManager $auth): Response
  {
    if (($authResponse = $auth->checkAuth($user, $request)) !== null)
      return $authResponse;

    $session = $request->getSession();
    $session->invalidate();
    return new JsonResponse(['message' => 'Session destroyed'], 200);
  }
}
