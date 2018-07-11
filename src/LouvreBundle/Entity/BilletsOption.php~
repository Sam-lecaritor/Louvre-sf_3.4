<?php

namespace LouvreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * BilletsOption
 *
 * @ORM\Table(name="billets_option")
 * @ORM\Entity(repositoryClass="LouvreBundle\Repository\BilletsOptionRepository")
 */
class BilletsOption
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
     * @var int
     *
     * @ORM\Column(name="nombre", type="integer")
     * @Assert\Type("integer")
     */
    private $nombre;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date", type="date")
     * @Assert\Date()
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     * @Assert\DateTime()
     */

    private $dateCreation;

    /**
     * @var string
     *
     * @ORM\Column(name="id_client", type="string", length=255)
     */

    private $idClient;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=255)
     *  @Assert\Email(
     *  message = "The email '{{ value }}' is not a valid email.",
     *  checkMX = true
     * )
     */
    private $mail;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param integer $nombre
     *
     * @return BilletsOption
     */
    public function setNombre($nombre)
    {
        $this->nombre = intval($nombre);

        return $this;
    }

    /**
     * Get nombre
     *
     * @return int
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set date
     *
     * @param \Date $date
     *
     * @return BilletsOption
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \Date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return BilletsOption
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set idClient
     *
     * @param string $idClient
     *
     * @return BilletsOption
     */
    public function setIdClient($idClient)
    {
        $this->idClient = $idClient;

        return $this;
    }

    /**
     * Get idClient
     *
     * @return string
     */
    public function getIdClient()
    {
        return $this->idClient;
    }

    /**
     * Set mail
     *
     * @param string $mail
     *
     * @return BilletsOption
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    public function __construct()
    {
        $now = new \Datetime();

        $id = uniqid('Lvr_');

        $this->setDateCreation($now);
        $this->setIdClient($id);
        
    }


}
