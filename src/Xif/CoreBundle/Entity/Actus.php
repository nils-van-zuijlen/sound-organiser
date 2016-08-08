<?php

namespace Xif\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xif\UserBundle\Entity\User;

/**
 * Actus
 *
 * @ORM\Table(name="xif_actus")
 * @ORM\Entity(repositoryClass="Xif\CoreBundle\Repository\ActusRepository")
 */
class Actus
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
	 * @var \DateTime
	 *
	 * @ORM\Column(name="date_publication", type="datetime")
	 */
	private $datePublication;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="contenu", type="text")
	 */
	private $contenu;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="titre", type="string", length=255)
	 */
	protected $titre;

	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="Xif\UserBundle\Entity\User")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $auteur;


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
	 * Set datePublication
	 *
	 * @param \DateTime $datePublication
	 *
	 * @return Actus
	 */
	public function setDatePublication($datePublication)
	{
		$this->datePublication = $datePublication;

		return $this;
	}

	/**
	 * Get datePublication
	 *
	 * @return \DateTime
	 */
	public function getDatePublication()
	{
		return $this->datePublication;
	}

	/**
	 * Set contenu
	 *
	 * @param string $contenu
	 *
	 * @return Actus
	 */
	public function setContenu($contenu)
	{
		$this->contenu = $contenu;

		return $this;
	}

	/**
	 * Get contenu
	 *
	 * @return string
	 */
	public function getContenu()
	{
		return $this->contenu;
	}

	/**
	 * Set auteur
	 *
	 * @param \User $auteur
	 *
	 * @return Actus
	 */
	public function setAuteur(User $auteur)
	{
		$this->auteur = $auteur;

		return $this;
	}

	/**
	 * Get auteur
	 *
	 * @return User
	 */
	public function getAuteur()
	{
		return $this->auteur;
	}

	public function __construct()
	{
		$this->datePublication = new \DateTime();
	}

	/**
	 * Set titre
	 *
	 * @param string $titre
	 *
	 * @return Actus
	 */
	public function setTitre($titre)
	{
		$this->titre = $titre;

		return $this;
	}

	/**
	 * Get titre
	 *
	 * @return string
	 */
	public function getTitre()
	{
		return $this->titre;
	}
}
