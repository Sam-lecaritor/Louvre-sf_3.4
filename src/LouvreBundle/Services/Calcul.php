<?php
namespace LouvreBundle\Services;

class Calcul
{


    public function calculAge($dateNaissance)
    {
        $now = new \Datetime();
        $age = $dateNaissance->diff($now)->y;

        return $age;
    }

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
            $prix = $prix / 2;
        }

        return $prix;
    }

}
