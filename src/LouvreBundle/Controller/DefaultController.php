<?php

namespace LouvreBundle\Controller;

use LouvreBundle\Entity\Billet;
use LouvreBundle\Entity\BilletsOption;
use LouvreBundle\Entity\TicketsCollection;
use LouvreBundle\Form\BilletsOptionType;
use LouvreBundle\Form\TicketsCollectionType;
use LouvreBundle\Services\Calcul;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//use Symfony\Component\Routing\Annotation\Route;

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
//todo => ajouter la suppression de l'option billet dans la bdd

        $session = $request->getSession();
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
        $message_alert = null;
        $message_info = null;
        $message_success = null;
        // $this->sendMail();
        /*  $session->set('mail', null); */

        // var_dump($session);

        if (null === ($session->get('option'))) {

            $step = 1;

            $form = $this->get('form.factory')->create(BilletsOptionType::class, $billetsOption);
            $formulaire = $form->createView();

            if ($request->isMethod('POST')) {

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $session->set('option', $billetsOption);

//todo ==> persister en base de données les billets mis en option************

                    $em->persist($billetsOption);
                    $em->flush();

                }
            }
        }

        if (null !== ($session->get('option')) && null === ($session->get('commande'))) {

            $step = 2;
            var_dump($session->get('option')->getIdClient());
            var_dump($session->get('option')->getDate());

            $collection->setClientId($session->get('option')->getIdClient());
            $collection->setClientMail($session->get('option')->getMail());
            $collection->setDate($session->get('option')->getDate());

            $collection->setConfirmed(false);
            $message_info = "Vous avez reservé {$session->get('option')->getNombre()} billets pour cette adresse mail : {$session->get('option')->getMail()}";

            $form = $this->get('form.factory')->create(TicketsCollectionType::class, $collection);
            $formulaire = $form->createView();

            if ($request->isMethod('POST')) {

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $form = $this->get('form.factory')->create(TicketsCollectionType::class, $collection);
                    $formulaire = $form->createView();

                    //todo => gerer la validation des formulaires
                    //todo=> gerer les erreurs de stripe

                    $nombreBillets = $form->getData()->getBillets()->count();
                    $billetsForm = $session->get('option')->getNombre();
                    $message_alert = $this->verifCommande($nombreBillets, $billetsForm);

                    if ($this->verifCommande($nombreBillets, $billetsForm) === null) {

                        $listeBillets = $collection->getBillets();
                        $CalculService = new Calcul;

                        foreach ($listeBillets as $ticket) {

                            if (!$ticket->getTarif()) {
                                $ticket->setTarif('false');
                            } else {
                                $ticket->setTarif('true');
                            }

                            $prixUnitaire = $CalculService->calculPrixBillet($ticket->getDateNaissance(), $ticket->getDemiJournee(), $ticket->getTarif());
                            $ticket->setPrixUnitaire($prixUnitaire);
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

            //todo = > persister la commande******
            //todo => gerer le cas de la charge a 0 euro***********

            $message_info = "Vous avez commandé {$session->get('commande')->getBillets()->count()} billets pour un prix total de : {$prixTotal} €";

/* $session->set('option', null);
$session->set('commande', null); */


            if ($request->isMethod('POST')) {

                if (isset($_POST["stripeToken"])) {

                    \Stripe\Stripe::setApiKey("sk_test_5KsDT1yflgRbhvzGrZUEdXl1");

                    $token = $_POST["stripeToken"];
                    $charge = \Stripe\Charge::create([
                        'amount' => $prixTotal * 100,
                        'currency' => 'eur',
                        'description' => 'Billetterie Louvre',
                        'source' => $token,
                    ]);

                    $statut = $charge->getLastResponse()->json["paid"];
                    $token = null;
                    $_POST["stripeToken"] = null;

                    echo "<div class='debug'>";
                    echo "CHARGE ===>";
                    var_dump($charge->getLastResponse()->json["paid"]);
                    echo "</br>";
                    echo "</div>";

                    if ($statut) {

                        $message_success = true;
                        $commande->setConfirmed(1);
                        $em->persist($commande);
                        $em->flush();
                        //todo => supprimer les billets en option en rapport a la commande

                        $this->sendMail($billets, $commande);

                        $session->set('option', null);
                        $session->set('commande', null);

                    }

                } 

            }
        }

        return $this->render('louvre/billet.html.twig', [
            'form' => $formulaire,
            'step' => $step,
            'messages' => array('test', 'retest'),
            'message_alert' => $message_alert,
            'message_info' => $message_info,
            'message_success' => $message_success,
        ]);

    }

    /**
     * Lists all billet
     *
     * @Route("/testbillets", name="test_billet")
     * @Method("GET")
     */
    public function testBilletsAction(Request $request)
    {
        $dateChoisie = $request->query->get('date');

        //todo => créer la methode de calcul de dates restantes

        $testDate = $this->getDoctrine()
            ->getRepository('LouvreBundle:BilletsOption')
            ->compterOptions($dateChoisie);

        $response = new Response(json_encode(array('placesRestantes' => 900, 'date' => $testDate)));
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
            ->setTo('samclarisse@live.fr')
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

//liste des methodes de arraycollection
    //https:www.doctrine-project.org/api/collections/latest/Doctrine/Common/Collections/ArrayCollection.html
    /*
    echo "<div class='debug'>";
    echo "TOKEN STRIPE ===>";
    var_dump($token);
    echo "</br>";
    echo "</div>";
     */

//4242 4242 4242 4242 test@mail.com 12 / 19 123 86000
    //4242 4242 4242 4242

}
