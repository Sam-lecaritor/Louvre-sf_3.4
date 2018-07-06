<?php

namespace LouvreBundle\Controller;

use LouvreBundle\Entity\Billet;
use LouvreBundle\Entity\BilletsOption;
use LouvreBundle\Entity\TicketsCollection;
use LouvreBundle\Form\BilletsOptionType;
use LouvreBundle\Form\TicketsCollectionType;
use LouvreBundle\Services\Calcul;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/billets")
     */
    public function billetsAction(Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        $billetsOption = new BilletsOption();
        $collection = new TicketsCollection();
        /*  $session->set('mail', null); */

        if (null === ($session->get('mail'))) {
            $step = 1;

            $form = $this->get('form.factory')->create(BilletsOptionType::class, $billetsOption);
            $formulaire = $form->createView();

            if ($request->isMethod('POST')) {

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $session->set('mail', $billetsOption->getMail());
                    $session->set('date_visite', $billetsOption->getDate());
                    $session->set('nbr_visite', $billetsOption->getNombre());
                    $session->set('id_option', $billetsOption->getIdClient());

/*                     $em = $this->getDoctrine()->getManager();
$em->persist($billetsOption);
$em->flush(); */

/* $session->set('idClient', $billetsOption['idClient']);
$test = $session->get('idClient'); */

                }
            }
        }

        if (null !== ($session->get('mail')) && null === ($session->get('commande'))) {
            $step = 2;

            $form = $this->get('form.factory')->create(TicketsCollectionType::class, $collection);

            $collection->setClientId($session->get('id_option'));
            $collection->setClientMail($session->get('mail'));
            $collection->setConfirmed(false);
            $formulaire = $form->createView();

            if ($request->isMethod('POST')) {

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $billetListe[] = $form->getData();

                    /*  echo "<div class='debug'>"; */
                    //nombre de tickets dans le formulaire
                    //liste des methodes de arraycollection
                    //https: //www.doctrine-project.org/api/collections/latest/Doctrine/Common/Collections/ArrayCollection.html

/*                     var_dump(count($form->getData()->getBillets()->toArray()));

echo "</br>";

echo "</div>"; */

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
                        $session->set('commande', $collection);

                        echo "<div class='debug'>";
                        echo "prix unitaire du billet => $prixUnitaire €";
                        echo "</br>";
                        echo "</div>";
                        /*  $em->persist($ticket); */

                    }
                    /* $em->flush(); */

                    echo "<div class='debug'>";
                    echo "prix total de la commande => {$collection->getPrixTotal()} €";
                    echo "</br>";

                    echo "</div>";

                } else {
                    echo "<div class='debug'>";
                    echo "echec de la validation du formulaire";
                    echo "</br>";
                    echo "</div>";

                }

            }
        }

        if (null !== ($session->get('commande'))) {
            $step = 3;
            $formulaire = null;
            if ($request->isMethod('POST')) {

                if (isset($_POST["stripeToken"])) {

                    \Stripe\Stripe::setApiKey("sk_test_5KsDT1yflgRbhvzGrZUEdXl1");

                    echo "<div class='debug'>";
                    echo "TOKEN STRIPE ===>";
                    var_dump($_POST["stripeToken"]);
                    echo "</br>";
                    echo "</div>";

                    $token = $_POST["stripeToken"];

                    $charge = \Stripe\Charge::create([
                        'amount' => 999,
                        'currency' => 'eur',
                        'description' => 'Billetterie Louvre',
                        'source' => $token,
                    ]);

                    echo "<div class='debug'>";
                    echo "CHARGE ===>";
                    var_dump($charge->getLastResponse()->json["paid"]);
                    echo "</br>";
                    echo "</div>";

                }

            }
        }
//4242 4242 4242 4242 test@mail.com 12 / 19 123 86000
//4242 4242 4242 4242

/* $session->getFlashBag()->add('notice', 'Profile updated');

// retrieve messages
foreach ($session->getFlashBag()->get('notice', array()) as $message) {
echo '<div class="flash-notice">' . $message . '</div>';
} */

        return $this->render('louvre/billet.html.twig', [
            'form' => $formulaire,
            'step' => $step,
            'messages' => array('test', 'retest'),
        ]);

    }

}
/*
 * 
 * 
 * retour d'une requette a l'api stripe 
 * 
 */





/* { ["headers"]=> array(14) { 
    ["Server"]=> string(5) "nginx" 
    ["Date"]=> string(29) "Fri, 06 Jul 2018 14:12:12 GMT"
     ["Content-Type"]=> string(16) "application/json" 
     ["Content-Length"]=> string(4) "1761" 
     ["Connection"]=> string(10) "keep-alive" 
     ["Access-Control-Allow-Credentials"]=> string(4) "true" 
     ["Access-Control-Allow-Methods"]=> string(32) "GET, POST, HEAD, OPTIONS, DELETE"
     ["Access-Control-Allow-Origin"]=> string(1) "*" 
     ["Access-Control-Expose-Headers"]=> string(104) "Request-Id, Stripe-Manage-Version, X-Stripe-External-Auth-Required, X-Stripe-Privileged-Session-Required" 
     ["Access-Control-Max-Age"]=> string(3) "300" 
     ["Cache-Control"]=> string(18) "no-cache, no-store" 
     ["Request-Id"]=> string(18) "req_MI8vKOnK2FDm0t" 
     ["Stripe-Version"]=> string(10) "2018-05-21" 
     ["Strict-Transport-Security"]=> string(44) "max-age=31556926; includeSubDomains; preload" }


["body"]=> string(1761) "{ "id": "ch_1CkufQBzWdn3KufvSN5mTEa3", "object": "charge", "amount": 999, "amount_refunded": 0, "application": null, "application_fee": null, "balance_transaction": "txn_1CkufQBzWdn3KufvBlTE052A", "captured": true, "created": 1530886332, "currency": "eur", "customer": null, "description": "Billetterie Louvre", "destination": null, "dispute": null, "failure_code": null, "failure_message": null, "fraud_details": {}, "invoice": null, "livemode": false, "metadata": {}, "on_behalf_of": null, "order": null, "outcome": { "network_status": "approved_by_network", "reason": null, "risk_level": "normal", "seller_message": "Payment complete.", "type": "authorized" }, "paid": true, "receipt_email": null, "receipt_number": null, "refunded": false, "refunds": { "object": "list", "data": [], "has_more": false, "total_count": 0, "url": "/v1/charges/ch_1CkufQBzWdn3KufvSN5mTEa3/refunds" }, "review": null, "shipping": null, "source": { "id": "card_1CkufOBzWdn3KufvWV2X3gZK", "object": "card", "address_city": null, "address_country": null, "address_line1": null, "address_line1_check": null, "address_line2": null, "address_state": null, "address_zip": "86000", "address_zip_check": "pass", "brand": "Visa", "country": "US", "customer": null, "cvc_check": "pass", "dynamic_last4": null, "exp_month": 12, "exp_year": 2019, "fingerprint": "h577L0mMRUmP6YyA", "funding": "credit", "last4": "4242", "metadata": {}, "name": null, "tokenization_method": null }, "source_transfer": null, "statement_descriptor": null, "status": "succeeded", "transfer_group": null } 
["json"]=> array(35) { ["id"]=> string(27) "ch_1CkufQBzWdn3KufvSN5mTEa3" ["object"]=> string(6) "charge" ["amount"]=> int(999) ["amount_refunded"]=> int(0) ["application"]=> NULL ["application_fee"]=> NULL ["balance_transaction"]=> string(28) "txn_1CkufQBzWdn3KufvBlTE052A" ["captured"]=> bool(true) ["created"]=> int(1530886332) ["currency"]=> string(3) "eur" ["customer"]=> NULL ["description"]=> string(18) "Billetterie Louvre" ["destination"]=> NULL ["dispute"]=> NULL ["failure_code"]=> NULL ["failure_message"]=> NULL ["fraud_details"]=> array(0) { } ["invoice"]=> NULL ["livemode"]=> bool(false) ["metadata"]=> array(0) { } ["on_behalf_of"]=> NULL ["order"]=> NULL ["outcome"]=> array(5) { ["network_status"]=> string(19) "approved_by_network" ["reason"]=> NULL ["risk_level"]=> string(6) "normal" ["seller_message"]=> string(17) "Payment complete." ["type"]=> string(10) "authorized" } ["paid"]=> bool(true) ["receipt_email"]=> NULL ["receipt_number"]=> NULL ["refunded"]=> bool(false) ["refunds"]=> array(5) { ["object"]=> string(4) "list" ["data"]=> array(0) { } ["has_more"]=> bool(false) ["total_count"]=> int(0) ["url"]=> string(47) "/v1/charges/ch_1CkufQBzWdn3KufvSN5mTEa3/refunds" } ["review"]=> NULL ["shipping"]=> NULL ["source"]=> array(23) { ["id"]=> string(29) "card_1CkufOBzWdn3KufvWV2X3gZK" ["object"]=> string(4) "card" ["address_city"]=> NULL ["address_country"]=> NULL ["address_line1"]=> NULL ["address_line1_check"]=> NULL ["address_line2"]=> NULL ["address_state"]=> NULL ["address_zip"]=> string(5) "86000" ["address_zip_check"]=> string(4) "pass" ["brand"]=> string(4) "Visa" ["country"]=> string(2) "US" ["customer"]=> NULL ["cvc_check"]=> string(4) "pass" ["dynamic_last4"]=> NULL ["exp_month"]=> int(12) ["exp_year"]=> int(2019) ["fingerprint"]=> string(16) "h577L0mMRUmP6YyA" ["funding"]=> string(6) "credit" ["last4"]=> string(4) "4242" ["metadata"]=> array(0) { } ["name"]=> NULL ["tokenization_method"]=> NULL } ["source_transfer"]=> NULL ["statement_descriptor"]=> NULL ["status"]=> string(9) "succeeded" ["transfer_group"]=> NULL } ["code"]=> int(200) }  */