<?php
namespace LouvreBundle\Handler;

use LouvreBundle\Services\MessagesGenerator;
use LouvreBundle\Services\Outils;

class HandlerReservations
{
    private $outils;
    private $messages;

    public function __construct(Outils $outils)
    {

        $this->outils = $outils;
        $this->messages = new MessagesGenerator();

    }

    public function reservationsVerif($form, $nbroptions, $collection, $session, $demiJourObligatoire)
    {

        $nombreBillets = $form->getData()->getBillets()->count();

        $result = $this->outils->verifCommande($nombreBillets, $nbroptions);
        if (null !== $result) {
            return $result;
        } else {
            $this->outils->hydrateCommande($collection, $demiJourObligatoire);

        }
        $erreurTarif = $this->outils->checkPrixTotal($session, $collection);
        if (null !== $erreurTarif) {
            return $erreurTarif;
        } else {
            return null;
        }

    }

}
