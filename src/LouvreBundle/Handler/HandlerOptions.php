<?php
namespace LouvreBundle\Handler;

use Doctrine\ORM\EntityManagerInterface;
use LouvreBundle\Services\MessagesGenerator;
use LouvreBundle\Services\Outils;

class HandlerOptions
{
    private $em;
    private $outils;
    private $messages;
    private $joursOff;
    private $datesOff;

    public function __construct(Outils $outils, EntityManagerInterface $em, $joursOff, $datesOff)
    {
        $this->em = $em;
        $this->outils = $outils;
        $this->mesages = new MessagesGenerator();
        $this->joursOff = $joursOff;
        $this->datesOff = $datesOff;


    }


    public function optionCheck($session, $dateChoisie, $billetsOption)
    {
        $dayMonth = $this->outils->stringYMDToDateDM($dateChoisie);
        $day = $this->outils->stringYMDToDay($dateChoisie);

        if (in_array($day, $this->joursOff)|| in_array($dayMonth, $this->datesOff)) {

            return $this->mesages->jourOff($dateChoisie);

        } elseif ($this->outils->calculBilletsRestants($dateChoisie) <= 0) {
            return $this->mesages->jourFull($dateChoisie);

        } else {
            $session->set('option', $billetsOption);
            $this->em->persist($billetsOption);
            $this->em->flush();

            return null;

        }

    }

}
