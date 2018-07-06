<?php

namespace LouvreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use LouvreBundle\Entity\Billet;

/**
 * TicketsCollection
 *
 * @ORM\Table(name="tickets_collection")
 * @ORM\Entity(repositoryClass="LouvreBundle\Repository\TicketsCollectionRepository")
 */
class TicketsCollection
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", length=255)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="client_mail", type="string", length=255)
     */
    private $clientMail;

    /**
     * @var int
     *
     * @ORM\Column(name="prix_total", type="integer")
     */
    private $prixTotal;

    /**
     * @var bool
     *
     * @ORM\Column(name="confirmed", type="boolean")
     */
    private $confirmed;

    /**
     * @ORM\OneToMany(targetEntity="LouvreBundle\Entity\Billet", mappedBy="TicketsCollection", cascade={"persist", "remove"})
     *
     */

    private $billets;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    public function __construct()
    {
        $this->billets = new ArrayCollection();

    }



    /**
     * Set clientId
     *
     * @param string $clientId
     *
     * @return TicketsCollection
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get clientId
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientMail
     *
     * @param string $clientMail
     *
     * @return TicketsCollection
     */
    public function setClientMail($clientMail)
    {
        $this->clientMail = $clientMail;

        return $this;
    }

    /**
     * Get clientMail
     *
     * @return string
     */
    public function getClientMail()
    {
        return $this->clientMail;
    }

    /**
     * Set prixTotal
     *
     * @param integer $prixTotal
     *
     * @return TicketsCollection
     */
    public function setPrixTotal($prixTotal)
    {
        $this->prixTotal = $prixTotal;

        return $this;
    }

    /**
     * Get prixTotal
     *
     * @return int
     */
    public function getPrixTotal()
    {
        return $this->prixTotal;
    }
    /**
     * Incremente prixTotal
     *
     * @param integer $prixUnitaireBillet
     *
     * @return TicketsCollection
     */
    public function incrementePrixTotal($prixUnitaireBillet)
    {
        $this->prixTotal += $prixUnitaireBillet;

        return $this;
    }



    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return TicketsCollection
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return bool
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }


    /**
     * Add billet
     *
     * @param \src/LouvreBundle\Entity\Billet $billet
     *
     * @return TicketsCollection
     */
    public function addBillet(Billet $billet)
    {
        $this->billets[] = $billet;

        return $this;
    }

    /**
     * Remove billet
     *
     * @param \src/LouvreBundle\Entity\Billet $billet
     */
    public function removeBillet(Billet $billet)
    {
        $this->billets->removeElement($billet);
    }

    /**
     * Get billets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBillets()
    {
        return $this->billets;
    }



}
