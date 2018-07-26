<?php
namespace Tests\LouvreBundle\Services;

use Louvrebundle\Services\Calcul;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CalculTest extends KernelTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testDouble()
    {
        $calcul = new Calcul($this->entityManager);

        $this->assertequals(4, $calcul->double(2));

    }

/**
 * calcul le prix d'un billet selon les parametres fournis
 * @var $datenaissance (date), $demijour(bool), $reduction(bool)
 * @return integer
 */

    public function testCalculPrixBilletTroisAns()
    {
        //pour trois ans le prix du billet doit etre egale a 0
        $my_date_time = time("Y-m-d H:i:s");
        $my_new_date_time = $my_date_time + 60 * 60 * 1 * 24 * 365 * 3;
        $my_new_date = date("Y-m-d H:i:s", $my_new_date_time);
        $dateNaissance = new \Datetime($my_new_date);

        $demiJour = 0;
        $reduction = 0;

        $calcul = new Calcul($this->entityManager);

        $this->assertequals(0, $calcul->calculPrixBillet($dateNaissance, $demiJour, $reduction));

    }

    public function testCalculPrixBilletSixAns()
    {
        //pour six ans le prix du billet doit etre egale a 8
        $my_date_time = time("Y-m-d H:i:s");
        $my_new_date_time = $my_date_time + 60 * 60 * 1 * 24 * 365 * 6;
        $my_new_date = date("Y-m-d H:i:s", $my_new_date_time);
        $dateNaissance = new \Datetime($my_new_date);

        $demiJour = 0;
        $reduction = 0;

        $calcul = new Calcul($this->entityManager);

        $this->assertequals(8, $calcul->calculPrixBillet($dateNaissance, $demiJour, $reduction));

    }

    public function testCalculPrixBilletDouzeAns()
    {
        //pour douze ans le prix du billet doit etre egale a 16
        $my_date_time = time("Y-m-d H:i:s");
        $my_new_date_time = $my_date_time + 60 * 60 * 1 * 24 * 366 * 12; // 12 ans + 12 jours
        $my_new_date = date("Y-m-d H:i:s", $my_new_date_time);
        $dateNaissance = new \Datetime($my_new_date);

        $demiJour = 0;
        $reduction = 0;

        $calcul = new Calcul($this->entityManager);

        $this->assertequals(16, $calcul->calculPrixBillet($dateNaissance, $demiJour, $reduction));

    }
    public function testCalculPrixBilletSenior()
    {
        //pour les + de 60 ans le prix du billet doit etre egale a 12
        $my_date_time = time("Y-m-d H:i:s");
        $my_new_date_time = $my_date_time + 60 * 60 * 1 * 24 * 366 * 60;
        $my_new_date = date("Y-m-d H:i:s", $my_new_date_time);
        $dateNaissance = new \Datetime($my_new_date);

        $demiJour = 0;
        $reduction = 0;

        $calcul = new Calcul($this->entityManager);

        $this->assertequals(12, $calcul->calculPrixBillet($dateNaissance, $demiJour, $reduction));

    }
    public function testCalculPrixBilletSeniorReduction()
    {
        //pour les + de 60 ans le prix du billet doit etre egale a 12 sauf reduction (10)
        $my_date_time = time("Y-m-d H:i:s");
        $my_new_date_time = $my_date_time + 60 * 60 * 1 * 24 * 366 * 60;
        $my_new_date = date("Y-m-d H:i:s", $my_new_date_time);
        $dateNaissance = new \Datetime($my_new_date);

        $demiJour = 0;
        $reduction = true;

        $calcul = new Calcul($this->entityManager);

        $this->assertequals(10, $calcul->calculPrixBillet($dateNaissance, $demiJour, $reduction));

    }
        public function testCalculPrixBilletAdultesReduction()
    {
        //pour les + de 12 ans le prix du billet doit etre egale a 12 sauf reduction (10)
        $my_date_time = time("Y-m-d H:i:s");
        $my_new_date_time = $my_date_time + 60 * 60 * 1 * 24 * 366 * 30;
        $my_new_date = date("Y-m-d H:i:s", $my_new_date_time);
        $dateNaissance = new \Datetime($my_new_date);

        $demiJour = 0;
        $reduction = true;

        $calcul = new Calcul($this->entityManager);

        $this->assertequals(10, $calcul->calculPrixBillet($dateNaissance, $demiJour, $reduction));

    }

        public function testCalculPrixBilletEnfantsReduction()
    {
        //pour les - de 12 ans le prix du billet doit etre egale a 8 la reduction n'est pas prise en compte
        $my_date_time = time("Y-m-d H:i:s");
        $my_new_date_time = $my_date_time + 60 * 60 * 1 * 24 * 365 * 11;
        $my_new_date = date("Y-m-d H:i:s", $my_new_date_time);
        $dateNaissance = new \Datetime($my_new_date);

        $demiJour = 0;
        $reduction = true;

        $calcul = new Calcul($this->entityManager);

        $this->assertequals(8, $calcul->calculPrixBillet($dateNaissance, $demiJour, $reduction));

    }





    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
