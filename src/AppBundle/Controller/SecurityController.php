<?php
/**
 * Created by PhpStorm.
 * User: giorgiopagnoni
 * Date: 04/07/16
 * Time: 12:47
 */

namespace AppBundle\Controller;

use AppBundle\Form\Security\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     *
     * @return Response
     */
    public function loginAction()
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('homepage'));
        }
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginType::class, [
            '_username' => $lastUsername,
        ]);

        return $this->render(
            'user/security/login.html.twig', [
                'form' => $form->createView(),
                'error' => $error,
            ]
        );
    }

    /**
     * @Route("/connect/facebook", name="connect_facebook")
     *
     * @return Response
     */
    public function facebookConnectAction()
    {
        return $this->get('oauth2.registry')
            ->getClient('facebook_main')
            ->redirect();
    }

    /**
     * @Route("/connect/facebook/check", name="connect_facebook_check")
     *
     * @param $request Request
     */
    public function connectFacebookCheckAction(Request $request)
    {
        // nope, see FacebookAuthenticator
    }

    /**
     * @Route("/connect/google", name="connect_google")
     *
     * @return Response
     */
    public function googleConnectAction()
    {
        return $this->get('oauth2.registry')
            ->getClient('google_main')
            ->redirect();
    }

    /**
     * @Route("/connect/google/check", name="connect_google_check")
     *
     * @param $request Request
     */
    public function connectGoogleCheckAction(Request $request)
    {
        // nope, see GoogleAuthenticator
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {
        throw new \Exception("this should not be reached");
    }

}