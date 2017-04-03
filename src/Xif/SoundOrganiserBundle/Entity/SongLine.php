<?php

namespace Xif\SoundOrganiserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SongLine
 *
 * @ORM\Table(name="xif_songline")
 * @ORM\Entity(repositoryClass="Xif\SoundOrganiserBundle\Repository\SongLineRepository")
 */
class SongLine implements \JsonSerializable
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
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="string", length=255)
	 */
	private $description;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="type", type="string", length=255)
	 */
	private $type;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="trans1", type="string", length=255, nullable=true)
	 */
	private $trans1;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="trans2", type="string", length=255)
	 */
	private $trans2;

	/**
	 * @ORM\Column(name="vol", type="float")
	 */
	protected $vol;

	/**
	 * @ORM\ManyToOne(targetEntity="Xif\SoundOrganiserBundle\Entity\Project", inversedBy="songLines")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $project;

	/**
	 * @ORM\ManyToOne(targetEntity="Xif\FileBundle\Entity\MultipleFile")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $multipleFile;


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
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return SongLine
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return SongLine
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
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return SongLine
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set trans1
	 *
	 * @param string $trans1
	 *
	 * @return SongLine
	 */
	public function setTrans1($trans1)
	{
		$this->trans1 = $trans1;

		return $this;
	}

	/**
	 * Get trans1
	 *
	 * @return string
	 */
	public function getTrans1()
	{
		return $this->trans1;
	}

	/**
	 * Set trans2
	 *
	 * @param string $trans2
	 *
	 * @return SongLine
	 */
	public function setTrans2($trans2)
	{
		$this->trans2 = $trans2;

		return $this;
	}

	/**
	 * Get trans2
	 *
	 * @return string
	 */
	public function getTrans2()
	{
		return $this->trans2;
	}

	/**
	 * Set project
	 *
	 * @param \Xif\SoundOrganiserBundle\Entity\Project $project
	 *
	 * @return SongLine
	 */
	public function setProject(\Xif\SoundOrganiserBundle\Entity\Project $project)
	{
		$this->project = $project;

		return $this;
	}

	/**
	 * Get project
	 *
	 * @return \Xif\SoundOrganiserBundle\Entity\Project
	 */
	public function getProject()
	{
		return $this->project;
	}

	/**
	 * Set multipleFile
	 *
	 * @param \Xif\FileBundle\Entity\MultipleFile $multipleFile
	 *
	 * @return SongLine
	 */
	public function setMultipleFile(\Xif\FileBundle\Entity\MultipleFile $multipleFile = null)
	{
		$this->multipleFile = $multipleFile;

		return $this;
	}

	/**
	 * Get multipleFile
	 *
	 * @return \Xif\FileBundle\Entity\MultipleFile
	 */
	public function getMultipleFile()
	{
		return $this->multipleFile;
	}

	/**
	 * Set vol
	 *
	 * @param string $vol
	 *
	 * @return SongLine
	 */
	public function setVol($vol)
	{
		$this->vol = $vol;

		return $this;
	}

	/**
	 * Get vol
	 *
	 * @return string
	 */
	public function getVol()
	{
		return $this->vol;
	}

	public function jsonSerialize()
	{
		$array = array(
			'name'  => $this->name,
			'file'  => ($this->multipleFile === null) ? false : $this->multipleFile->getId(),
			'vol'   => $this->vol,
			'trans' => array(
				$this->type,
				($this->trans1 === null) ? '' : $this->trans1,
				$this->trans2
				),
			'descr' => $this->description,
			'id'    => $this->id
			);
		return $array;
	}
}
