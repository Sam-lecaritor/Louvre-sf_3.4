<?php
namespace LouvreBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use LouvreBundle\Services\MessagesGenerator;

//use LouvreBundle\Services\Calcul;

class Outils
{

    private $em;
    private $maxBilletsJour;

    public function __construct(EntityManagerInterface $em, $maxBilletsJour)
    {
        $this->em = $em;
        $this->maxBilletsJour = $maxBilletsJour;

    }

/**
 * initialise le calendrier pour l'etape 1 des formulaires
 * retourne un tableau de dates au format string
 */

    public function initialiseDatePicker()
    {
        $result = $this->em->getRepository('LouvreBundle:Billet')->countByDate();
        $datePicktable = [];
        if (null !== $result) {
            foreach ($result as $key => $value) {

                if (intval($value["nombre"]) >= $this->maxBilletsJour) {
                    $dateFormate = substr($value["date"], 8, 2) . '/' . substr($value["date"], 5, 2) . '/' . substr($value["date"], 0, 4);
                    $datePicktable[] = $dateFormate;
                }
            }
        }
        return implode(',', $datePicktable);
    }

/**
 * verifie les billets en option dont la validité a expiré
 *
 */
    public function checkOptions($session)
    {

        $id_client = null;
        $oldOptions = $this->em
            ->getRepository('LouvreBundle:BilletsOption')
            ->findOptionsByExpiration($this->dureeValiditeeOption());

        if (null !== ($session->get('option'))) {
            $id_client = $session->get('option')->getIdClient();
        }

        if ($oldOptions !== null && $id_client !== null) {
            foreach ($oldOptions as $oldOption) {
                foreach ($oldOption as $key => $value) {

                    if ($value === $id_client) {
                        $this->deleteOption($id_client);
                        $session->set('option', null);
                        $session->set('commande', null);
                        $step = 1;

                    } else {
                        $this->deleteOption($value);
                    }

                }
            }
        }
    }

/**
 * supprime les billets en option par leur id
 *
 * @param [type] $id
 * @return void
 */
    public function deleteOption($id)
    {
        $result = $this->em->getRepository('LouvreBundle:BilletsOption')->findOptionByIdClient($id);
        if ($result) {
            $this->em->remove($result);
            $this->em->flush();
        }
    }

/**
 * retourne le mois et le jour en chiffres d'une date
 *
 * @param [string] $dateChoisie
 * @return string
 */
    public function stringYMDToDateDM($dateChoisie)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $dateChoisie);
        return $date->format('d-m');
    }

/**
 * retourne le jour en lettre d'une date
 *
 * @param [string] $dateChoisie
 * @return string
 */

    public function stringYMDToDay($dateChoisie)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $dateChoisie);
        return $date->format('l');

    }

/**
 * verifie si le nombre de billets commandés correspond aux billets mis en option
 *
 * @param [type] $nombreBillets
 * @param [type] $billetsForm
 * @return string
 */
    public function verifCommande($nombreBillets, $billetsForm)
    {
        $message = new MessagesGenerator();
        if ($nombreBillets === 0) {
            $message_alert = $message->emptyForm();

        } elseif ($nombreBillets < $billetsForm) {
            $message_alert = $message->toFewTickets($nombreBillets, $billetsForm);

        } elseif ($nombreBillets > $billetsForm) {
            $message_alert = $message->toManyTickets($nombreBillets, $billetsForm);

        } else {
            $message_alert = null;
        }
        return $message_alert;
    }

    /**
     * Undocumented function
     *
     * @param [type] $collection
     * @param [type] $demiJourObligatoire
     * @return void
     */
    public function hydrateCommande($collection, $demiJourObligatoire)
    {
        $listeBillets = $collection->getBillets();
        foreach ($listeBillets as $ticket) {

            if ($demiJourObligatoire) {
                $ticket->setDemiJournee(1);
            }

            $prixUnitaire = $this->calculPrixBillet($ticket->getDateNaissance(), $ticket->getDemiJournee(), $ticket->getTarif());
            $ticket->setPrixUnitaire($prixUnitaire);

            $ticket->setDate($collection->getDate());

            $collection->incrementePrixTotal($prixUnitaire);
        }

    }

//verifie si le prix de la commande est supperieur a 0 euro
    //retourne un message d'erreur si prix < 0
    public function checkPrixTotal($session, $collection)
    {
     $prix = $collection->getPrixTotal();
        $message = new MessagesGenerator();

        if ($prix > 0) {
            $session->set('commande', $collection);
            return null;
        } else {
            $message_alert = $message->erreurPrix();
            return $message_alert;
        }

    }

/**
 * Ajoute le nombre d'heures pour la validité des options
 *
 * @return array $datetime
 */
    public function dureeValiditeeOption()
    {

        $my_date_time = time("Y-m-d H:i:s");

// ( 1h30 = 5400 secondes soit 60X60X1.5)
        $my_new_date_time = $my_date_time - (60 * 60 * 2.5);

        $my_new_date = date("Y-m-d H:i:s", $my_new_date_time);
        return $my_new_date;

    }

/**
 * Calcul l'age du client en années
 *
 * @param [date] $dateNaissance
 * @return integer
 */
    public function calculAge($dateNaissance)
    {
        $now = new \Datetime();
        $age = $dateNaissance->diff($now)->y;

        return $age;
    }

/**
 * calcul le prix d'un billet selon les parametres fournis
 * @var $datenaissance (date), $demijour(bool), $reduction(bool)
 * @return integer
 */

    public function calculPrixBillet($dateNaissance, $demiJour, $reduction)
    {
        $age = $this->calculAge($dateNaissance);

        $prix = 0;
        if ($age < 4) {
            $prix = 0;
        } elseif ($age > 4 && $age <= 11) {
            $prix = 8;
        } elseif ($age >= 60) {
            $prix = 12;
        } else {
            $prix = 16;
        }
        if ($reduction === true && $prix > 10) {
            $prix = 10;
        }

        return $prix;
    }

    /**
     *
     * calcul le nombre de billets restants pour une date donnée
     * @var $date
     * @return integer
     */

    public function calculBilletsRestants($date)
    {

        $testDateOptions = $this->em
            ->getRepository('LouvreBundle:BilletsOption')
            ->compterOptions($date);

        $testdateBillets = $this->em
            ->getRepository('LouvreBundle:Billet')
            ->compterBilletsJour($date);

        return $this->maxBilletsJour - ($testdateBillets + $testDateOptions);
    }

    /**
     *
     * determine si l'option demi jour est obligatoire
     * @var $date
     * @return boolean
     */

    public function calculDemiJourObligatoire($date)
    {
        $now = new \Datetime();
        $nowDMY = $now->format('d-m-y');
        $dateDMY = $date->format('d-m-y');

        if ($nowDMY === $dateDMY && intval($now->format('H')) >= 14) {
            return true;
        } else {
            return false;
        }

    }

    public function hydrateCollection($collection, $options)
    {

        $collection->setClientId($options->getIdClient());
        $collection->setClientMail($options->getMail());
        $collection->setDate($options->getDate());
        $collection->setConfirmed(false);

    }

    /**
     * Fonction de test pour php-unit
     *
     * @param [type] $int
     * @return void
     */
    public function double($int)
    {
        return $int * 2;
    }

}
