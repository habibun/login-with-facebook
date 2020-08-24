<?php

namespace App\Controller;

use App\Service\FbInterface;
use App\Service\FbJs;
use App\Service\FbPhp;
use Facebook\Facebook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if (!session_id()) {
            session_start();
        }
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        //facebook
        $appId = $this->getParameter('fb.app_id');
        $appSecret = $this->getParameter('fb.app_secret');
        $fb = new Facebook([
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'default_graph_version' => 'v2.10',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['email']; // Optional permissions
        $fbLoginUrl = $helper->getLoginUrl('https://localhost:8000/fb-callback', $permissions);

        return $this->render('security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
                'fb_login_url' => $fbLoginUrl,
                'app_id' => $appId,
                'app_secret' => $appSecret,
            ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/fb-callback", name="fb_callback")
     *
     * @param FbPhp $fbPhp
     * @return RedirectResponse
     */
    public function fbCallback(FbPhp $fbPhp)
    {
        if (!session_id()) {
            session_start();
        }

        $url = $this->generateUrl('fb_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $url .= '?fbId='.$fbPhp->getGraphUser()->getId();

        return $this->redirect($url);
    }

    /**
     * @Route("/fb-login", name="fb_login")
     *
     * @param FbPhp $fbPhp
     */
    public function fbLogin(FbPhp $fbPhp)
    {
    }

    /**
     * @Route("/fb-callback-js", name="fb_callback_js")
     */
    public function fbCallbackJs(FbJs $fbJs)
    {
        if (!session_id()) {
            session_start();
        }

        dd($fbJs->getGraphUser());
    }
}
