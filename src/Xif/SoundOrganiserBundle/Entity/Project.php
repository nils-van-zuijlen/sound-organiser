<?php

namespace Xif\SoundOrganiserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xif\SoundOrganiserBundle\Entity\SongLine;

/**
 * Project
 *
 * @ORM\Table(name="xif_project")
 * @ORM\Entity(repositoryClass="Xif\SoundOrganiserBundle\Repository\ProjectRepository")
 */
class Project
{
	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @ORM\Column(name="date_creation", type="datetime")
	 */
	protected $dateCreation;

	/**
	 * @ORM\Column(name="description", type="string", length=255)
	 */
	protected $description;

	/**
	 * @ORM\ManyToOne(targetEntity="Xif\UserBundle\Entity\User", inversedBy="projects")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $owner;

	/**
	 * @ORM\OneToMany(targetEntity="Xif\SoundOrganiserBundle\Entity\SongLine", mappedBy="project", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected $songLines;


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
	 * Set title
	 *
	 * @param string $title
	 *
	 * @return Project
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	public function __construct() {
		$this->dateCreation = new \DateTime();
	}

	/**
	 * Set dateCreation
	 *
	 * @param \DateTime $dateCreation
	 *
	 * @return Project
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
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return Project
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set owner
	 *
	 * @param \Xif\UserBundle\Entity\User $owner
	 *
	 * @return Project
	 */
	public function setOwner(\Xif\UserBundle\Entity\User $owner)
	{
		$this->owner = $owner;

		$owner->addProject($this);

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return \Xif\UserBundle\User
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	public function getJson() {
		$json = '{ "name": "' .
			$this->title .
			'", "path": "", "vol_factor": 0.7, "songs": [';
		foreach ($this->songLines as $line) {
			$json .= $line->getJson();
		}
		$json .= '] }';

		return $json;
	}

	/**
	 * Add songLine
	 *
	 * @param \Xif\SoundOrganiserBundle\Entity\SongLine $songLine
	 *
	 * @return Project
	 */
	public function addSongLine(SongLine $songLine)
	{
		$this->songLines[] = $songLine;
		$songLine->setProject($this);

		return $this;
	}

	/**
	 * Remove songLine
	 *
	 * @param \Xif\SoundOrganiserBundle\Entity\SongLine $songLine
	 */
	public function removeSongLine(SongLine $songLine)
	{
		$this->songLines->removeElement($songLine);
	}

	/**
	 * Get songLines
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSongLines()
	{
		return $this->songLines;
	}
}
