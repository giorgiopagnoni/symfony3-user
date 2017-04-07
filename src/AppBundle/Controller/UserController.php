<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\User\EditType;
use AppBundle\Form\User\RegistrationType;
use AppBundle\Form\User\RequestPasswordType;
use AppBundle\Form\User\ResetPasswordType;
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

        $form = $this->createForm(RegistrationType::class, null, ['captcha_type' => $this->getParameter('app.captcha_type')]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $token = $this->get('user.token_generator')->generateToken();

            /** @var User $user */
            $user = $form->getData();
            $user->setToken($token);
            $user->setIsActive(false);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            if ($this->getParameter('app.double_opt_in')) {
                $this->get('user.mailer')->sendActivationEmailMessage($user);
                $this->addFlash('success', 'user.activation-link');
                return $this->redirect($this->generateUrl('homepage'));
            }

            return $this->redirect($this->generateUrl('user_activate', ['token' => $token]));
        }

        return $this->render('user/register.html.twig', [
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
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'user.update.success');

            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView()
        ]);
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

        $this->addFlash('success', 'user.welcome');

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

        $form = $this->createForm(RequestPasswordType::class, null, ['captcha_type' => $this->getParameter('app.captcha_type')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = $this->getDoctrine()->getRepository(User::class);

            /** @var User $user */
            $user = $repository->findOneBy(['email' => $form->get('_username')->getData(), 'isActive' => true]);
            if (!$user) {
                $this->addFlash('warning', 'user.not-found');
                return $this->render('user/request-password-reset.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            $token = $this->get('user.token_generator')->generateToken();
            $user->setToken($token);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('user.mailer')->sendResetPasswordEmailMessage($user);

            $this->addFlash('success', 'user.request-password-link');
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('user/request-password-reset.html.twig', [
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

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setToken(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'user.update.success');

            // automatic login
            return $this->get('security.authentication.guard_handler')
                ->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $this->get('user.security.login_form_authenticator'),
                    'main'
                );

        }

        return $this->render('user/password-reset.html.twig', ['form' => $form->createView()]);
    }

}
