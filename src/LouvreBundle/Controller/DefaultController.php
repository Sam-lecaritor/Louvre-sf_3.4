<?php

namespace LouvreBundle\Controller;

use LouvreBundle\Entity\Billet;
use LouvreBundle\Entity\BilletsOption;
use LouvreBundle\Form\BilletsOptionType;
use LouvreBundle\Form\BilletType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

        $billet = new Billet();
        $billetsOption = new BilletsOption();
        /*  $session->set('mail', null); */

        if (null === ($session->get('mail'))) {
            $step = 1;

            $form = $this->get('form.factory')->create(BilletsOptionType::class, $billetsOption);

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

        if (null !== ($session->get('mail'))) {
            $step = 2;

            $form = $this->get('form.factory')->create(BilletType::class, $billet);

            if ($request->isMethod('POST')) {

                $form->handleRequest($request);


                if ($form->isSubmitted() && $form->isValid()) {
/* 
                    $billets['birthday'] = 'iii';
                    $billets['nom'] = '';
                    $billets['prenom'] = 'kkkkk';
                    $billets['pay'] = 'oooooooooooooooooo';
                    $billets['id'] = '';
                    $billets['tarif'] = '';
                    $billets['duree'] = 'mmmmmmmmmmmm';

                    $billetListe[]= 'kkkkk'; */
                   // array_push($billetListe, $billets);

$billetListe[] = $form->getData();


                    var_dump($billetListe);

                } else {
                    echo ($session->get('mail'));

                }
            }
        }

/* $session->getFlashBag()->add('notice', 'Profile updated');

// retrieve messages
foreach ($session->getFlashBag()->get('notice', array()) as $message) {
echo '<div class="flash-notice">' . $message . '</div>';
} */

        return $this->render('louvre/billet.html.twig', [
            'form' => $form->createView(),
            'step' => $step,
        ]);

    }

}
