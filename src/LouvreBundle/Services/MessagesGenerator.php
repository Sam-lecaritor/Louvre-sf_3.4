<?php
namespace LouvreBundle\Services;

class MessagesGenerator
{

    /**
     * Messages d'information sur les jours off du musée
     *
     * @return string
     */
    public function jourOff($dateChoisie)
    {
        return 'Le musée du Louvre est fermé le jour que vous avez selectionné (' . $dateChoisie . '), veuillez choisir une date différente';
    }

    /**
     * Messages d'information sur les jours complets du musée
     *
     * @return string
     */

    public function jourFull($dateChoisie)
    {
        return 'Le musée du Louvre est complet le jour que vous avez selectionné (' . $dateChoisie . '), veuillez choisir une date différente';

    }

    /**
     * message d'info etape 2 "reservation des billets"
     *
     * @param [type] $nbrOptions
     * @param [type] $mail
     * @param [type] $date
     * @return string
     */
    public function getInfosReservation($nbrOptions, $mail, $date)
    {

        return 'Vous avez réservé ' . $nbrOptions . ($nbrOptions > 1 ? " billets " : " billet ") . 'pour cette adresse mail : ' . $mail . ' pour le ' . $date->format('d-m-Y');
    }
//message formulaire billets vide
    public function emptyForm()
    {
        return "Vous n'avez pas renseigné le formulaire";

    }
//message nombre de billets inferrieur a la mise en option
    public function toFewTickets($nombreBillets, $billetsForm)
    {
        return "Le nombre de billets renseignés ( {$nombreBillets} ) est inferieur aux nombre de billets que vous avez réservé précédemment ( {$billetsForm} )";

    }
//message nombre de billets supperieurs a la mise en option
    public function toManyTickets($nombreBillets, $billetsForm)
    {
        return "Le nombre de billets renseignés ( {$nombreBillets} ) est supperieur aux nombre de billets que vous avez réservé précédemment ( {$billetsForm} )";

    }

 //message prix billet null   
    public function erreurPrix()
    {
        return "Vous ne pouvez pas passer une commande de 0 €, veuillez verifier les dates de naissance indiquée.";

    }
//message d'info sur la commande finale
    public function messageInfosFinal($count, $prix, $date)
    {
        return 'Vous avez commandé ' . $count . ($count > 1 ? " billets " : " billet ") . 'pour un prix total de : ' . $prix . '€' . ' date de visite : ' . $date;

    }

}
