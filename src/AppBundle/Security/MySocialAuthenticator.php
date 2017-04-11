<?php
/**
 * Created by PhpStorm.
 * User: giorgiopagnoni
 * Date: 06/04/17
 * Time: 11:27
 */

namespace AppBundle\Security;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MySocialAuthenticator extends SocialAuthenticator
{
    private $clientRegistry;
    private $em;
    private $router;

    /**
     * MySocialAuthenticator constructor.
     * @param ClientRegistry $clientRegistry
     * @param EntityManager $em
     * @param RouterInterface $router
     */
    public function __construct(ClientRegistry $clientRegistry, EntityManager $em, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public function getCredentials(Request $request)
    {
        if ($request->getPathInfo() == '/connect/facebook/check') {
            return [
                'token' => $this->fetchAccessToken($this->getFacebookClient()),
                'service' => 'facebook'
            ];
        }

        if ($request->getPathInfo() == '/connect/google/check') {
            return [
                'token' => $this->fetchAccessToken($this->getGoogleClient()),
                'service' => 'google'
            ];
        }

        // don't auth
        return null;
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $getClient = 'get' . ucfirst($credentials['service'] . 'Client');
        $socialUser = $this->$getClient()
            ->fetchUserFromToken($credentials['token']);

        // Have they logged in with that social before?
        $socialId = $credentials['service'] .'Id';
        $existingUser = $this->em->getRepository('AppBundle:User')
            ->findOneBy([$socialId => $socialUser->getId()]);
        if ($existingUser) {
            return $existingUser;
        }

        // Do we have a matching user by email?
        $email = $socialUser->getEmail();
        $user = $this->em->getRepository('AppBundle:User')
            ->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setIsActive(true);
            $user->setPlainPassword(md5(uniqid()));
        }

        $setId = 'set' . ucfirst($socialId);
        $user->$setId($socialUser->getId());
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @return OAuth2Client
     */
    private function getFacebookClient()
    {
        return $this->clientRegistry->getClient('facebook_main');
    }

    /**
     * @return OAuth2Client
     */
    private function getGoogleClient()
    {
        return $this->clientRegistry->getClient('google_main');
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->router->generate('security_login');
        return new RedirectResponse($url);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // TODO: Implement onAuthenticationFailure() method.
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->router->generate('homepage'));
    }

}