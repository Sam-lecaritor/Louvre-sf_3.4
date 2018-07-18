<?php

namespace LouvreBundle\Controller;

use LouvreBundle\Entity\Billet;
use LouvreBundle\Entity\BilletsOption;
use LouvreBundle\Entity\TicketsCollection;
use LouvreBundle\Form\BilletsOptionType;
use LouvreBundle\Form\TicketsCollectionType;
use LouvreBundle\Services\Calcul;
use LouvreBundle\Services\StripeLouvre;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;


//todo => gerer la validation des formulaires
//todo=> gerer les erreurs de stripe

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

/*  $datepickObject= new DatepickerConfig();
$datepick = $datepickObject->getConfig();  */

        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $billetsOption = new BilletsOption();
        $collection = new TicketsCollection();
        $billetSimple = new Billet();
        $message_alert = null;
        $message_info = null;
        $message_success = null;
        $message_failed = null;
        $datepickConf =[];
        $calculService = $this->get('calcul');

        $this->checkOptions($session);


        if (null === ($session->get('option'))) {

            $step = 1;
            $datepickConf =$this->initialiseDatePicker();

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
            $message_info = "Vous avez reservé {$session->get('option')->getNombre()} billets pour cette adresse mail : {$session->get('option')->getMail()}";
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

                            ($ticket->getTarif() ? 0 : 1);
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
            $message_info = "Vous avez commandé {$billets->count()} billets pour un prix total de : {$prixTotal} €";

            if ($request->isMethod('POST')) {

                if (isset($_POST["stripeToken"])) {

                    $stripe = new StripeLouvre();

                    $statut = $stripe->createCharge($prixTotal, $_POST["stripeToken"]);

                    $_POST["stripeToken"] = null;

                    if ($statut) {

                        $message_success = true;
                        $commande->setConfirmed(1);
                        $em->persist($commande);
                        $em->flush();
//todo => supprimer les billets en option en rapport a la commande

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
            'messages' => array('test', 'retest'),
            'message_alert' => $message_alert,
            'message_info' => $message_info,
            'message_success' => $message_success,
            'message_failed' => $message_failed,
            'datepickConf' =>$datepickConf
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
        return  implode(',',$datePicktable);

    }

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

/* CHARGE ===>
object(Stripe\ApiResponse)#935 (4)
{ ["headers"]=> array(14) { ["Server"]=> string(5) "nginx" ["Date"]=> string(29) "Thu, 12 Jul 2018 08:11:32 GMT" ["Content-Type"]=> string(16) "application/json" ["Content-Length"]=> string(4) "1763" ["Connection"]=> string(10) "keep-alive" ["Access-Control-Allow-Credentials"]=> string(4) "true" ["Access-Control-Allow-Methods"]=> string(32) "GET, POST, HEAD, OPTIONS, DELETE" ["Access-Control-Allow-Origin"]=> string(1) "*" ["Access-Control-Expose-Headers"]=> string(104) "Request-Id, Stripe-Manage-Version, X-Stripe-External-Auth-Required, X-Stripe-Privileged-Session-Required" ["Access-Control-Max-Age"]=> string(3) "300" ["Cache-Control"]=> string(18) "no-cache, no-store" ["Request-Id"]=> string(18) "req_P7DME6aChW1Qff" ["Stripe-Version"]=> string(10) "2018-05-21" ["Strict-Transport-Security"]=> string(44) "max-age=31556926; includeSubDomains; preload" }

["body"]=> string(1763) "{ "id": "ch_1CmztgBzWdn3KufviHctDaOf", "object": "charge", "amount": 10000, "amount_refunded": 0, "application": null, "application_fee": null, "balance_transaction": "txn_1CmztgBzWdn3KufvcEMIwgkZ", "captured": true, "created": 1531383092, "currency": "eur", "customer": null, "description": "Billetterie Louvre", "destination": null, "dispute": null, "failure_code": null, "failure_message": null, "fraud_details": {}, "invoice": null, "livemode": false, "metadata": {}, "on_behalf_of": null, "order": null, "outcome": { "network_status": "approved_by_network", "reason": null, "risk_level": "normal", "seller_message": "Payment complete.", "type": "authorized" }, "paid": true, "receipt_email": null, "receipt_number": null, "refunded": false, "refunds": { "object": "list", "data": [], "has_more": false, "total_count": 0, "url": "/v1/charges/ch_1CmztgBzWdn3KufviHctDaOf/refunds" }, "review": null, "shipping": null, "source": { "id": "card_1CmzteBzWdn3KufvZLpdrtbB", "object": "card", "address_city": null, "address_country": null, "address_line1": null, "address_line1_check": null, "address_line2": null, "address_state": null, "address_zip": "11111", "address_zip_check": "pass", "brand": "Visa", "country": "US", "customer": null, "cvc_check": "pass", "dynamic_last4": null, "exp_month": 12, "exp_year": 2021, "fingerprint": "h577L0mMRUmP6YyA", "funding": "credit", "last4": "4242", "metadata": {}, "name": null, "tokenization_method": null }, "source_transfer": null, "statement_descriptor": null, "status": "succeeded", "transfer_group": null } "

["json"]=> array(35) {
["id"]=> string(27) "ch_1CmztgBzWdn3KufviHctDaOf"
["object"]=> string(6) "charge"
["amount"]=> int(10000)
["amount_refunded"]=> int(0)
["application"]=> NULL
["application_fee"]=> NULL
["balance_transaction"]=> string(28) "txn_1CmztgBzWdn3KufvcEMIwgkZ"
["captured"]=> bool(true)
["created"]=> int(1531383092)
["currency"]=> string(3) "eur"
["customer"]=> NULL
["description"]=> string(18) "Billetterie Louvre"
["destination"]=> NULL
["dispute"]=> NULL
["failure_code"]=> NULL
["failure_message"]=> NULL
["fraud_details"]=> array(0) { }
["invoice"]=> NULL
["livemode"]=> bool(false)
["metadata"]=> array(0) { }
["on_behalf_of"]=> NULL
["order"]=> NULL
["outcome"]=> array(5) {
["network_status"]=> string(19) "approved_by_network"
["reason"]=> NULL
["risk_level"]=> string(6) "normal"
["seller_message"]=> string(17) "Payment complete."
["type"]=> string(10) "authorized" }
["paid"]=> bool(true)
["receipt_email"]=> NULL
["receipt_number"]=> NULL
["refunded"]=> bool(false)
["refunds"]=> array(5) {
["object"]=> string(4) "list"
["data"]=> array(0) { }
["has_more"]=> bool(false)
["total_count"]=> int(0)
["url"]=> string(47) "/v1/charges/ch_1CmztgBzWdn3KufviHctDaOf/refunds" }
["review"]=> NULL
["shipping"]=> NULL
["source"]=> array(23) {
["id"]=> string(29) "card_1CmzteBzWdn3KufvZLpdrtbB"
["object"]=> string(4) "card"
["address_city"]=> NULL
["address_country"]=> NULL
["address_line1"]=> NULL
["address_line1_check"]=> NULL
["address_line2"]=> NULL
["address_state"]=> NULL
["address_zip"]=> string(5) "11111"
["address_zip_check"]=> string(4) "pass"
["brand"]=> string(4) "Visa"
["country"]=> string(2) "US"
["customer"]=> NULL
["cvc_check"]=> string(4) "pass"
["dynamic_last4"]=> NULL
["exp_month"]=> int(12)
["exp_year"]=> int(2021)
["fingerprint"]=> string(16) "h577L0mMRUmP6YyA"
["funding"]=> string(6) "credit"
["last4"]=> string(4) "4242"
["metadata"]=> array(0) { }
["name"]=> NULL
["tokenization_method"]=> NULL }
["source_transfer"]=> NULL
["statement_descriptor"]=> NULL
["status"]=> string(9) "succeeded"
["transfer_group"]=> NULL }
["code"]=> int(200) }  */
