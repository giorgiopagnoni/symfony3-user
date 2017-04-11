<?php
/**
 * Created by PhpStorm.
 * User: giorgiopagnoni
 * Date: 03/07/16
 * Time: 14:24
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mailer
{
    protected $mailer;
    protected $router;
    protected $twig;
    protected $logger;
    protected $noreply;

    /**
     * Mailer constructor.
     * @param \Swift_Mailer $mailer
     * @param Router $router
     * @param \Twig_Environment $twig
     * @param LoggerInterface $logger
     * @param $noreply
     */
    public function __construct(\Swift_Mailer $mailer, Router $router, \Twig_Environment $twig, LoggerInterface $logger, $noreply)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->noreply = $noreply;
    }

    /**
     * @param User $user
     */
    public function sendActivationEmailMessage(User $user)
    {
        $url = $this->router->generate('user_activate', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = [
            'user' => $user,
            'activationUrl' => $url
        ];

        $this->sendMessage('user/email/register-done.html.twig', $context, $this->noreply, $user->getEmail());
    }

//    public function sendActivationDoneEmailMessage(User $user)
//    {
//        $template = $this->parameters['template']['activation-done'];
//        $login = $this->router->generate('security_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
//
//        $context = [
//            'user' => $user,
//            'loginUrl' => $login,
//        ];
//
//        $this->sendMessage($template, $context, $this->parameters['from_email'], $user->getEmail());
//    }

    /**
     * @param User $user
     */
    public function sendResetPasswordEmailMessage(User $user)
    {
        $url = $this->router->generate('user_reset_password', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = [
            'user' => $user,
            'resetPasswordUrl' => $url,
        ];

        $this->sendMessage('user/email/request-password.html.twig', $context, $this->noreply, $user->getEmail());
    }

    /**
     * @param $templateName string
     * @param $context array
     * @param $fromEmail string
     * @param $toEmail string
     * @return bool
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->load($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }
        $result = $this->mailer->send($message);

        $log_context = ['to' => $toEmail, 'message' => $textBody, 'template' => $templateName];
        if ($result) {
            $this->logger->info('SMTP email sent', $log_context);
        } else {
            $this->logger->error('SMTP email error', $log_context);
        }

        return $result;
    }
}