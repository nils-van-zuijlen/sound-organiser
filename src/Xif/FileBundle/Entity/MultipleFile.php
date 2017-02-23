<?php

namespace Xif\FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * MultipleFile
 *
 * @ORM\Table(name="xif_multiple_file")
 * @ORM\Entity(repositoryClass="Xif\FileBundle\Repository\MultipleFileRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MultipleFile
{
	const UPLOAD_ROOT_DIR = __DIR__.'/../Uploads/';

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
	 * @ORM\Column(name="filename", type="string", length=255)
	 */
	private $filename;

	/**
	 * @var \Xif\UserBundle\Entity\User
	 *
	 * @ORM\ManyToOne(targetEntity="Xif\UserBundle\Entity\User", inversedBy="files")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $owner;

	/**
	 * @var  File
	 * @ORM\OneToMany(targetEntity="Xif\FileBundle\Entity\File", mappedBy="group", cascade={"persist", "remove"})
	 */
	protected $files;

	/**
	 * @Assert\File(
	 *     maxSize="10M",
	 *     mimeTypes={"audio/mpeg", "audio/ogg", "audio/flac", "audio/x-flac"}
	 *   )
	 */
	protected $file;


	/**
	 * Constructor
	 */
	public function __construct(\Xif\UserBundle\Entity\User $owner = null)
	{
		$this->files = new \Doctrine\Common\Collections\ArrayCollection();
		$this->setOwner($owner);
	}

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
	 * Set filename
	 *
	 * @param string $filename
	 *
	 * @return MultipleFile
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;

		return $this;
	}

	/**
	 * Get filename
	 *
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Set owner
	 *
	 * @param \Xif\UserBundle\Entity\User $owner
	 *
	 * @return MultipleFile
	 */
	public function setOwner(\Xif\UserBundle\Entity\User $owner)
	{
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return \Xif\UserBundle\Entity\User
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	/**
	 * Add file
	 *
	 * @param \Xif\FileBundle\Entity\File $file
	 *
	 * @return MultipleFile
	 */
	public function addFile(\Xif\FileBundle\Entity\File $file)
	{
		$this->files[] = $file;
		$file->setGroup($this);

		return $this;
	}

	/**
	 * Remove file
	 *
	 * @param \Xif\FileBundle\Entity\File $file
	 */
	public function removeFile(\Xif\FileBundle\Entity\File $file)
	{
		$this->files->removeElement($file);
	}

	/**
	 * Get files
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getFiles()
	{
		return $this->files;
	}

	// Form and lifecycle callbacks

	public function setFile(UploadedFile $file)
	{
		$this->file = $file;
		
		return $this;
	}
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @ORM\PrePersist()
	 */
	public function preUpload()
	{
		$dbOriginalFile = new File;
		$dbOriginalFile->preUpload($this->file);
		$this->addFile($dbOriginalFile);
		$this->filename = $this->file->getClientOriginalName();
	}

	/**
	 * @ORM\PostPersist()
	 */
	public function upload()
	{
		$this->files[0]->upload($this->file);
	}

	/**
	 * @ORM\PreRemove()
	 */
	public function preRemoveUpload()
	{
		// On supprime la relation avec le propriétaire
		$this->owner->removeFile($this);
		// On devrait supprimer la relation avec les songline
	}
}
