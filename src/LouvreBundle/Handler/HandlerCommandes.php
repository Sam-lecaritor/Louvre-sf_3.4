<?php
namespace LouvreBundle\Handler;

use Doctrine\ORM\EntityManagerInterface;
use LouvreBundle\Services\Mail;
use LouvreBundle\Services\Outils;
use LouvreBundle\Services\StripeLouvre;

class HandlerCommandes
{
    private $em;
    private $outils;
    private $messages;
    private $mail;
    private $stripe;

    public function __construct(Outils $outils, EntityManagerInterface $em, Mail $mail, StripeLouvre $stripe)
    {
        $this->em = $em;
        $this->outils = $outils;
        $this->mail = $mail;
        $this->stripe = $stripe;

    }

    public function confirmPayement($token, $commande, $session)
    {

        $stripe = $this->stripe;
        $statut = $stripe->createCharge($commande->getPrixTotal(), $token);
        $_POST["stripeToken"] = null;

        if (isset($statut['paid'])) {

            //persistance des données
            $commande->setConfirmed(1);
            $this->em->persist($commande);
            $this->em->flush();
            //envoie du mail
            $this->mail->sendMail($commande);
            //effacement des traces de la commande
            $this->outils->deleteOption($session->get('option')->getIdClient());
            $session->set('option', null);
            $session->set('commande', null);

            return true;

        } else {
            return false;

        }

    }

}
