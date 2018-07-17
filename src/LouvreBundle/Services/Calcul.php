<?php
namespace LouvreBundle\Services;

use Doctrine\ORM\EntityManagerInterface;

class Calcul
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
        $my_new_date_time = $my_date_time - 60*60*2.5;

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
        if ($reduction === true) {
            $prix = 10;
        } elseif ($age < 4) {
            $prix = 0;
        } elseif ($age > 4 && $age < 12) {
            $prix = 8;
        } elseif ($age > 12 && $age < 60) {
            $prix = 16;
        } elseif ($age > 60) {
            $prix = 12;
        }

        if ($demiJour) {
            $prix = $prix;
        }

        return $prix;
    }

/**
 *
 * calcul le nombre de billets restant pour une date donnée
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

        return 1000 - ($testdateBillets + $testDateOptions);
    }

}
