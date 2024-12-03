<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AuthManager
{

    public function checkAuth(#[CurrentUser()] User $user, Request $request): ?JsonResponse
    {
        $session = $request->getSession();
        $tokenFromHeader = $request->headers->get('token_user');
        $tokenFromSession = $session->get('token_user');
        if (null === $user || !($tokenFromHeader == $tokenFromSession)) {
            return new JsonResponse(['message' => 'missing credentials',], 401);
        }
        return null;
    }
}
