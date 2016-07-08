<?php
/**
 * Created by PhpStorm.
 * User: giorgiopagnoni
 * Date: 03/07/16
 * Time: 14:24
 */

namespace UserBundle\Service;

use UserBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mailer
{
    const FROM_EMAIL = 'noreply@giorgiopagnoni.it';
    
    protected $mailer;
    protected $router;
    protected $twig;
    protected $logger;
    protected $noreply;

    public function __construct(\Swift_Mailer $mailer, Router $router, \Twig_Environment $twig, LoggerInterface $logger, $noreply)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->noreply = $noreply;
    }

    public function sendActivationEmailMessage(User $user)
    {
        $url = $this->router->generate('user_activate', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = [
            'user' => $user,
            'activationUrl' => $url
        ];

        $this->sendMessage('UserBundle:email:register-done.html.twig', $context, $this->noreply, $user->getEmail());
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

    public function sendResetPasswordEmailMessage(User $user)
    {
        $url = $this->router->generate('user.reset-password', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = [
            'user' => $user,
            'resetUrl' => $url,
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email'], $user->getEmail());
    }

    /**
     * @param $templateName
     * @param $context
     * @param $fromEmail
     * @param $toEmail
     * @return bool
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }
        $result = $this->mailer->send($message);

        $log_context = ['to' => $toEmail, 'message' => $textBody, 'template' => $templateName];
        if ($result) {
            $this->logger->addInfo('SMTP email sent', $log_context);
        } else {
            $this->logger->addError('SMTP email error', $log_context);
        }

        return $result;
    }
}