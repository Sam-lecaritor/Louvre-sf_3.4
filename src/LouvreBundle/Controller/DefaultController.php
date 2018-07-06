<?php

namespace LouvreBundle\Controller;

use LouvreBundle\Entity\Billet;
use LouvreBundle\Entity\BilletsOption;
use LouvreBundle\Entity\TicketsCollection;
use LouvreBundle\Form\BilletsOptionType;
use LouvreBundle\Form\TicketsCollectionType;
use LouvreBundle\Services\Calcul;
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

                echo "<div class='debug'>";
                echo "formulaire ===>";
                var_dump($request);
                echo "</br>";
                echo "</div>";

            }
        }

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
