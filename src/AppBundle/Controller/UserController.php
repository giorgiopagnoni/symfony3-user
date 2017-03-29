<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\EditType;
use AppBundle\Form\RegistrationType;
use AppBundle\Form\RequestPasswordType;
use AppBundle\Form\ResetPasswordType;
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

            $this->addFlash('success', $this->get('translator')->trans('user.activation-link'));
            return $this->redirect($this->generateUrl('homepage'));
//            return $this->redirect($this->generateUrl('user_register_done'));
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
        if ($form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('user.update.success'));

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

        $this->addFlash('success', $this->get('translator')->trans('user.welcome'));

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
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->get('user.mailer')->sendResetPasswordEmailMessage($user);
            }

            $this->addFlash('success', $this->get('translator')->trans('user.request-password-link'));
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

        if ($form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setToken(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('user.update.success'));

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
