<?php

namespace UserBundle\Controller;

use UserBundle\Entity\User;
use UserBundle\Form\EditType;
use UserBundle\Form\RegistrationType;
use UserBundle\Form\RequestPasswordType;
use UserBundle\Form\ResetPasswordType;
use UserBundle\Form\UserEditForm;
use UserBundle\Form\UserRegistrationForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     */
    public function registerAction(Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $form = $this->createForm(RegistrationType::class);

        $form->handleRequest($request);
        if ($form->isValid()) {

            $token = $this->get('user.token_generator')->generateToken();
            /** @var User $user */
            $user = $form->getData();
            $user->setToken($token);
            $user->setIsActive(false);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('user.mailer')->sendActivationEmailMessage($user);

            return $this->redirect($this->generateUrl('user_register_done'));
        }

        return $this->render('UserBundle::register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit", name="user_edit")
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Request $request)
    {
        $form = $this->createForm(EditType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Updated ' . $user->getEmail());

            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('UserBundle::edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/register-done", name="user_register_done")
     */
    public function registerDoneAction()
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('UserBundle::register-done.html.twig');
    }

    /**
     * @Route("/activate/{token}", name="user_activate")
     */
    public function activateAction(Request $request, User $user)
    {
        $user->setIsActive(true);
        $user->setToken(null);
        $user->setActivatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // TODO: is sending an email here really necessary...

        $this->addFlash('success', 'Welcome!');

        // automatic login
        return $this->get('security.authentication.guard_handler')
            ->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $this->get('user.security.login_form_authenticator'),
                'main'
            );
    }

    /**
     * @Route("/request-password-reset", name="user_request_password_reset")
     */
    public function requestPasswordResetAction(Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $form = $this->createForm(RequestPasswordType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $repository = $this->getDoctrine()->getRepository(User::class);
            /** @var User $user */
            $user = $repository->findOneBy(['email' => $form->getData()['_username'], 'isActive' => true]);
            if ($user) {
                $token = $this->get('user.token_generator')->generateToken();
                $user->setToken($token);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($user);
                $em->flush();
            }

            $this->get('user.mailer')->sendResetPasswordEmailMessage($user);

            $this->addFlash('success', 'A link has been sent to your inbox!');
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('UserBundle::request-password-reset.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/reset-password/{token}", name="user_reset_password")
     */
    public function resetPasswordAction(Request $request, User $user)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var User $formUser */
            $user = $form->getData();
            $user->setToken(null);

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Your password has been reset!');

            // automatic login
            return $this->get('security.authentication.guard_handler')
                ->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $this->get('user.security.login_form_authenticator'),
                    'main'
                );

        }

        return $this->render('UserBundle::password-reset.html.twig', ['form' => $form->createView()]);
    }
}
