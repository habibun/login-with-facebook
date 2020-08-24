<?php

namespace App\Service;

use App\Entity\User;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FbJs implements FbInterface
{
    /**
     * @var Facebook
     */
    private $params;

    /**
     * Fb constructor.
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function getFb()
    {
        return new Facebook([
            'app_id' => $this->params->get('fb.app_id'),
            'app_secret' => $this->params->get('fb.app_secret'),
            'default_graph_version' => 'v2.10',
        ]);
    }

    public function getAccessToken()
    {
        $fb = $this->getFb();
        $helper = $fb->getJavaScriptHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: '.$e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: '.$e->getMessage();
            exit;
        }

        if (!isset($accessToken)) {
            echo 'No cookie set or no OAuth data could be obtained from cookie.';
            exit;
        }

        return $accessToken;
    }

    public function getGraphUser()
    {
        $fb = $this->getFb();
        $accessToken = $this->getAccessToken();

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name', $accessToken);
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: '.$e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: '.$e->getMessage();
            exit;
        }

        return $response->getGraphUser();
    }

    public function getNewUser($fbId)
    {
        $user = new User();
        $user->setEmail('test@localhost.com');
        $user->setPassword('');
        $user->setRoles(['ROLE_USER']);
        $user->setFbId($fbId);

        return $user;
    }
}
