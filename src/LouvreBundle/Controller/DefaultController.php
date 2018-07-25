<?php

namespace LouvreBundle\Controller;

use LouvreBundle\Entity\Billet;
use LouvreBundle\Entity\BilletsOption;
use LouvreBundle\Entity\TicketsCollection;
use LouvreBundle\Form\BilletsOptionType;
use LouvreBundle\Form\TicketsCollectionType;
use LouvreBundle\Services\StripeLouvre;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {

        return $this->render('louvre/index.html.twig', [
            'test' => 'test',
        ]);
    }

    /**
     * @Route("/annulation")
     */

    public function annulationAction(Request $request)
    {

        $session = $request->getSession();
        $id = $session->get('option')->getIdClient();
        $this->deleteOption($id);

        $session->set('option', null);
        $session->set('commande', null);
        $step = 1;

        return $this->redirect('/billets');

    }

    /**
     * @Route("/billets")
     */
    public function billetsAction(Request $request)
    {

        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $billetsOption = new BilletsOption();
        $collection = new TicketsCollection();
        $billetSimple = new Billet();
        $message_alert = null;
        $message_info = null;
        $message_success = null;
        $message_failed = null;
        $billetsListeFinale= null;
        $demiJourObligatoire=null;
        $nbrOptions=null;
        $datepickConf = [];
        $calculService = $this->get('calcul');

        $this->checkOptions($session);

        if (null === ($session->get('option'))) {

            $step = 1;
            $datepickConf = $this->initialiseDatePicker();

            $form = $this->get('form.factory')->create(BilletsOptionType::class, $billetsOption);
            $formulaire = $form->createView();

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $dateChoisie = $billetsOption->getDate()->format('Y-m-d');
                    $placesReste = $calculService->calculBilletsRestants($dateChoisie);

                    if ($placesReste > 0) {
                        $message_alert = null;
                        $session->set('option', $billetsOption);
                        $em->persist($billetsOption);
                        $em->flush();

                    } else {
                        $message_alert = "Plus assez de places disponibles pour cette date ({$dateChoisie}), veuillez en choisir une autre";

                    }
                }
            }
        }

        if (null !== ($session->get('option')) && null === ($session->get('commande'))) {

            $step = 2;
            $collection->setClientId($session->get('option')->getIdClient());
            $collection->setClientMail($session->get('option')->getMail());
            $collection->setDate($session->get('option')->getDate());
            $collection->setConfirmed(false);
            $nbrOptions = $session->get('option')->getNombre();
            $message_info =
            'Vous avez réservé ' . $session->get('option')->getNombre() . ($session->get('option')->getNombre() > 1 ? " billets " : " billet ") . 'pour cette adresse mail : ' . $session->get('option')->getMail(). ' pour le ' . $session->get('option')->getDate()->format('d-M-Y') ;
            $demiJourObligatoire = $calculService->calculDemiJourObligatoire($session->get('option')->getDate());
            $session->set('demiJour', $demiJourObligatoire);

            $form = $this->get('form.factory')->create(TicketsCollectionType::class, $collection);
            $formulaire = $form->createView();

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $form = $this->get('form.factory')->create(TicketsCollectionType::class, $collection);
                    $formulaire = $form->createView();
                    $nombreBillets = $form->getData()->getBillets()->count();
                    $billetsForm = $session->get('option')->getNombre();
                    $message_alert = $this->verifCommande($nombreBillets, $billetsForm);

                    if ($this->verifCommande($nombreBillets, $billetsForm) === null) {
                        $listeBillets = $collection->getBillets();
                        $CalculService = $this->get('calcul');

                        foreach ($listeBillets as $ticket) {

                            //($ticket->getTarif() ? 0 : 1);
                            ($demiJourObligatoire ? $ticket->setDemiJournee(1): $ticket->setDemiJournee(0));
                            $prixUnitaire = $CalculService->calculPrixBillet($ticket->getDateNaissance(), $ticket->getDemiJournee(), $ticket->getTarif());
                            $ticket->setPrixUnitaire($prixUnitaire);
                            //$ticket->setCollectionId($collection->getClientId());
                            $ticket->setDate($collection->getDate());

                            $collection->incrementePrixTotal($prixUnitaire);
                        }

                        if ($collection->getPrixTotal() > 0) {
                            $session->set('commande', $collection);

                        } else {
                            $message_alert = "Vous ne pouvez pas passer une commande de 0 €, veuillez verifier les dates de naissance indiquée.";
                        }
                    }
                }
            }
        }

        if (null !== ($session->get('commande')) && null !== ($session->get('option'))) {
            $step = 3;
            $formulaire = null;
            $prixTotal = $session->get('commande')->getPrixTotal();
            $commande = $session->get('commande');
            $billets = $session->get('commande')->getBillets();
            $billetsListeFinale=$billets;
            $message_info = 'Vous avez commandé ' . $billets->count() . ($billets->count() > 1 ? " billets " : " billet ") . 'pour un prix total de : ' . $prixTotal . '€'. ' date de visite : ' . $session->get('option')->getDate()->format('d-M-Y');
           $demiJourObligatoire= $session->get('demiJour');


            if ($request->isMethod('POST')) {

                if (isset($_POST["stripeToken"])) {

                    $stripe = new StripeLouvre();
                    $statut = $stripe->createCharge($prixTotal, $_POST["stripeToken"]);
                    $_POST["stripeToken"] = null;

                    if (isset($statut['paid'])) {

                        $message_success = true;
                        $commande->setConfirmed(1);
                        $em->persist($commande);
                        $em->flush();
                        $this->sendMail($billets, $commande);
                        $id = $session->get('option')->getIdClient();
                        $this->deleteOption($id);
                        $session->set('option', null);
                        $session->set('commande', null);

                    } else {
                        $message_failed = true;

                    }
                }
            }
        }

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
            'nbrOptions' => $nbrOptions
        ]);
    }

    /**
     * Retourne le nombre de billets restants pour la date clické sur le calendrier
     *
     * @Route("/testbillets", name="test_billet")
     * @Method("GET")
     */
    public function testBilletsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dateChoisie = $request->query->get('date');
        $calculService = $this->get('calcul');
        $placesReste = $calculService->calculBilletsRestants($dateChoisie);

        $response = new Response(json_encode(array('placesRestantes' => $placesReste, 'date' => $placesReste)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * verifie si le nombre de billets commandés
     *  correspond aux billets en option
     *
     */

    public function verifCommande($nombreBillets, $billetsForm)
    {

        if ($nombreBillets === 0) {
            $message_alert = "Vous n'avez pas renseigné le formulaire";

        } elseif ($nombreBillets < $billetsForm) {
            $message_alert = "Le nombre de billets renseignés ( {$nombreBillets} ) est inferieur aux nombre de billets que vous avez réservé précédemment ( {$billetsForm} )";

        } elseif ($nombreBillets > $billetsForm) {
            $message_alert = "Le nombre de billets renseignés ( {$nombreBillets} ) est supperieur aux nombre de billets que vous avez réservé précédemment ( {$billetsForm} )";

        } else {
            $message_alert = null;
        }
        return $message_alert;
    }

    public function sendMail($billets, $commande)
    {
        $message = (new \Swift_Message('Musée du Louvre'));
        $cid = $message->embed(\Swift_Image::fromPath('images/louvre-pyramid-baniere.png'));
        $message->setFrom('send@example.com')
            ->setTo($commande->getClientMail())
            ->setBody(
                $this->renderView(
                    'louvre/Emails/confirmation.html.twig',
                    array('billets' => $billets,
                        'commande' => $commande,
                        'cid' => $cid)
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);
    }

/**
 * supprime une option par l'id client
 */

    public function deleteOption($id)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('LouvreBundle:BilletsOption')->findOptionByIdClient($id);
        if ($result) {
            $em->remove($result);
            $em->flush();
        }
    }

    public function checkOptions($session)
    {
        $em = $this->getDoctrine()->getManager();
        $calculService = $this->get('calcul');
        $id_client = null;
        $oldOptions = $em
            ->getRepository('LouvreBundle:BilletsOption')
            ->findOptionsByExpiration($calculService->dureeValiditeeOption());

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

    public function initialiseDatePicker()
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('LouvreBundle:Billet')->countByDate();
        $datePicktable = [];
        if (null !== $result) {
            foreach ($result as $key => $value) {

                if (intval($value["nombre"]) >= 1000) {
                    $dateFormate = substr($value["date"], 8, 2) . '/' . substr($value["date"], 5, 2) . '/' . substr($value["date"], 0, 4);
                    $datePicktable[] = $dateFormate;
                }
            }
        }
        return implode(',', $datePicktable);
    }
}
