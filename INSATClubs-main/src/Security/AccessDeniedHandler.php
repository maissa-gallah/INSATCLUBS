<?php


namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;


class AccessDeniedHandler implements AccessDeniedHandlerInterface
{    use TargetPathTrait;
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {$this->urlGenerator=$urlGenerator;


    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {

        return new RedirectResponse($this->urlGenerator->generate('error',[
    ]));
    }
}