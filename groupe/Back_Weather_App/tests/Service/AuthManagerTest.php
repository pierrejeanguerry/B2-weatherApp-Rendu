<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\AuthManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthManagerTest extends TestCase
{
    public function testCheckAuth()
    {
        // Mocking User entity
        $user = $this->createMock(User::class);

        // Mocking Request
        $request = $this->createMock(Request::class);

        // Mocking Session
        $session = $this->createMock(SessionInterface::class);
        $request->expects($this->once())
            ->method('getSession')
            ->willReturn($session);

        // Setting up the session to return a token
        $session->expects($this->once())
            ->method('get')
            ->with('token_user')
            ->willReturn('valid_token_here');

        // Setting up the request headers to return a token
        $headers = $this->createMock(\Symfony\Component\HttpFoundation\HeaderBag::class);
        $headers->expects($this->once())
            ->method('get')
            ->with('token_user')
            ->willReturn('valid_token_here');
        $request->headers = $headers;

        // Creating an instance of AuthManager
        $authManager = new AuthManager();

        // Invoking the checkAuth method
        $result = $authManager->checkAuth($user, $request);

        // Asserting that the result is true since authentication is successful
        $this->assertTrue($result);
    }
}
