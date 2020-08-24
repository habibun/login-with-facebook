<?php

namespace App\Security;

use App\Entity\User;
use App\Service\FbInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class FbAuthenticator extends AbstractGuardAuthenticator
{
    use TargetPathTrait;

    const LOGIN_ROUTE = 'fb_login';
    /**
     * @var FbInterface
     */
    private $fbPhp;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * FbAuthenticator constructor.
     * @param FbInterface $fbPhp
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(FbInterface $fbPhp, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator)
    {
        $this->fbPhp = $fbPhp;
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route');
    }

    public function getCredentials(Request $request)
    {
        $fbId = $request->query->get('fbId');

        return ['fb_id' => $fbId];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['fbId' => $credentials['fb_id']]);

        if (!$user) {
            $user = $this->fbPhp->getNewUser($credentials['fb_id']);
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // todo
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_homepage'));
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    public function supportsRememberMe()
    {
        return true;
    }
}
