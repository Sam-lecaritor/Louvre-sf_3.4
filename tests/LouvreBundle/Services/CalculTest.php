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
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
