<?php

namespace App\EntryPoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class ApiAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private $realmName;

    public function __construct($realmName)
    {
        $this->realmName = $realmName;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $content = array('success' => false, 'message' => 'Invalid API credentials');

        $response = new Response();
        $response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realmName));
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($content))
            ->setStatusCode(401);
        return $response;
    }
}
