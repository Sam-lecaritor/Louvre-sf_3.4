<?php

namespace LouvreBundle\Controller;

use LouvreBundle\Entity\Billet;
//use LouvreBundle\Services\Calcul;
use LouvreBundle\Services\Mail;
use LouvreBundle\Services\Outils;
use LouvreBundle\Entity\BilletsOption;
use LouvreBundle\Form\BilletsOptionType;
use LouvreBundle\Handler\HandlerOptions;
use LouvreBundle\Entity\TicketsCollection;
use LouvreBundle\Handler\HandlerCommandes;
use LouvreBundle\Form\TicketsCollectionType;
use LouvreBundle\Services\MessagesGenerator;
use LouvreBundle\Handler\HandlerReservations;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DefaultController extends Controller
{

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('louvre/index.html.twig', []);
    }

    /**
     * @Route("/billets")
     */
    public function billetsAction(Request $request, Outils $outils, HandlerOptions $handlerOptions,HandlerReservations $handlerReservations , HandlerCommandes $handlerCmd, MessagesGenerator $messagesGenerator, Mail $mail)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

/* verification de la validité des options
deja enregistrées dans la bdd, session en cours ou non */
        $outils->checkOptions($session);
//initialisation des variables
        $message_alert = null;
        $message_info = null;
        $message_success = null;
        $message_failed = null;
        $billetsListeFinale = null;
        $demiJourObligatoire = null;
        $nbrOptions = null;
        $datepickConf = [];
/**
 * etape 1 mise en option des billets souhaités par l'utilisateur
 *
 */
        if (null === ($session->get('option'))) {

            $step = 1;
            $billetsOption = new BilletsOption();
            $datepickConf = $outils->initialiseDatePicker();
            $form = $this->get('form.factory')->create(BilletsOptionType::class, $billetsOption);
            $formulaire = $form->createView();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $dateChoisie = $billetsOption->getDate()->format('Y-m-d');
                $message_alert = $handlerOptions->optionCheck($session, $dateChoisie, $billetsOption);
            }
        }
/**
 * etape 2 reservation du nombre de billets et renseignement des informations
 *
 */
        if (null !== $session->get('option') && null === $session->get('commande')) {

            $step = 2;
            $collection = new TicketsCollection();
            $options = $session->get('option');
            $nbrOptions = $options->getNombre();
            //hydratation de l'objet collection tickets
            $outils->hydrateCollection($collection, $options);
            //generation du message d'information sur la commande
            $message_info = $messagesGenerator->getInfosReservation($options);
            //parametrage de la demi journée obligatoire pour le formulaire de reservation
            $demiJourObligatoire = $outils->calculDemiJourObligatoire($session->get('option')->getDate());
            $session->set('demiJour', $demiJourObligatoire);

            $form = $this->get('form.factory')->create(TicketsCollectionType::class, $collection);
            $formulaire = $form->createView();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //recréer les données du formulaire pour le ressoumettre en cas d'echec des verifications malgré la validation des données des entitées
                $formulaire = $form->createView();
                
                //validation de la reservation
                $result = $handlerReservations->reservationsVerif($form, $options->getNombre(),$collection, $session, $demiJourObligatoire );
                if($result !== null){
                    $message_alert =$result;
                }
                

            }
        }
/**
 * etape 3 paiement et confirmation en cas de succes ou message d'erreur en cas d'echec
 *
 */
        if (null !== ($session->get('commande')) && null !== ($session->get('option'))) {
            $step = 3;
            $formulaire = null;
            $commande = $session->get('commande');
            $billetsListeFinale = $commande->getBillets();
            //message d'info sur le prix total et la date de la visite avant confirmation commande
            $message_info = $messagesGenerator->messageInfosFinal($billetsListeFinale->count(), $commande->getPrixTotal(), $session->get('option')->getDate()->format('d-m-Y'));

            if ($request->isMethod('POST') && isset($_POST["stripeToken"])) {
                if ($handlerCmd->confirmPayement($_POST["stripeToken"], $commande, $session)) {
                    $message_success = true;
                } else {
                    $message_failed = true;
                }
            }
        }
        //rendu de la vue avec twig
        return $this->render('louvre/billet.html.twig', [
            'form' => $formulaire,
            'step' => $step,
            'demiJourObligatoire' => $demiJourObligatoire,
            'billetsListeFinale' => $billetsListeFinale,
            'message_alert' => $message_alert,
            'message_info' => $message_info,
            'message_success' => $message_success,
            'message_failed' => $message_failed,
            'datepickConf' => $datepickConf,
            'nbrOptions' => $nbrOptions,
        ]);
    }

    /**
     * Retourne le nombre de billets restants pour la date clické sur le calendrier
     *
     * @Route("/testbillets", name="test_billet")
     * @Method("GET")
     */
    public function testBilletsAction(Request $request, Outils $outils)
    {
        $em = $this->getDoctrine()->getManager();
        $dateChoisie = $request->query->get('date');
        $placesReste = $outils->calculBilletsRestants($dateChoisie);
        $response = new Response(json_encode(array('placesRestantes' => $placesReste, 'date' => $placesReste)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Annule les commandes en option et les supprime de la bdd
     *
     * @Route("/annulation")
     */
    public function annulationAction(Request $request, Outils $outils)
    {
        $session = $request->getSession();
        $id = $session->get('option')->getIdClient();
        $outils->deleteOption($id);
        $session->set('option', null);
        $session->set('commande', null);
        $step = 1;
        return $this->redirect('/billets');
    }

}
