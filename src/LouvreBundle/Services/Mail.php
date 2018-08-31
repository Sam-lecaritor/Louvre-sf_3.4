<?php
namespace LouvreBundle\Services;


class Mail
{
    private $mailer;
    private $templating;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public function sendMail($commande)
    {
        $message = (new \Swift_Message('Musée du Louvre'));
        $cid = $message->embed(\Swift_Image::fromPath('images/louvre-pyramid-baniere.png'));
        $message->setFrom('send@example.com')
            ->setTo($commande->getClientMail())
            ->setBody(
                $this->templating->render(
                    'louvre/Emails/confirmation.html.twig',
                    array('billets' => $commande->getBillets(),
                        'commande' => $commande,
                        'cid' => $cid)
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }


}
