<?php
// src/Xif/FileBundle/Entity/File.php

namespace Xif\FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Xif\UserBundle\Entity\User;

/**
 * File
 *
 * @ORM\Table(name="xif_file")
 * @ORM\Entity(repositoryClass="Xif\FileBundle\Repository\FileRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class File
{
	const UPLOAD_ROOT_DIR = '/var/www/html/SoundOrganiser/src/Xif/FileBundle/Uploads/';

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(name="extension", type="string", length=255)
	 */
	protected $extension;

	/**
	 * @ORM\Column(name="mime_type", type="string", length=255)
	 */
	protected $mimeType;

	/**
	 * @ORM\ManyToOne(targetEntity="Xif\UserBundle\Entity\User", inversedBy="files")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $owner;

	/**
	 * @ORM\Column(name="original_name", type="string", length=255)
	 */
	protected $originalName;

	protected $tempFilename;

	/**
	 * @Assert\File(
	 *     maxSize="10M",
	 *     mimeTypes={"audio/mpeg", "audio/ogg"}
	 *   )
	 */
	protected $file;
	protected $potOwner;


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
	 * Set owner
	 *
	 * @param \Xif\UserBundle\Entity\User $owner
	 *
	 * @return Project
	 */
	public function setOwner(User $owner)
	{
		$this->owner = $owner;

		$owner->addFile($this);

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
	 * Set extension
	 *
	 * @param string $extension
	 *
	 * @return File
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;

		return $this;
	}

	/**
	 * Get extension
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	public function __construct($user = null)
	{
		if ($user !== null) {
			$this->potOwner = $user;
		}
	}

	/**
	 * Méthodes pour l'upload de fichiers
	 */

	public function getLocation()
	{
		return self::UPLOAD_ROOT_DIR . $this->id . '.' . $this->extension;
	}

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
		$this->extension    = $this->file->guessExtension();
		$this->mimeType     = $this->file->getMimeType();
		$this->originalName = $this->file->getClientOriginalName();
		$this->setOwner($this->potOwner);
	}

	/**
	 * @ORM\PostPersist()
	 */
	public function upload()
	{
		$this->file->move(
			self::UPLOAD_ROOT_DIR,
			$this->id . '.' . $this->extension
			);
	}

	/**
	 * @ORM\PreRemove()
	 */
	public function preRemoveUpload()
	{
		// On sauvegarde temporairement le nom du fichier, car il dépend de l'id
		$this->tempFilename = $this->getLocation();
		// On supprime la relation avec le propriétaire
		$this->owner->removeFile($this);
		// On devrait supprimer la relation avec les songline
	}

	/**
	 * @ORM\PostRemove()
	 */
	public function removeUpload()
	{
		// En PostRemove, on n'a pas accès à l'id, on utilise notre nom sauvegardé
		if (file_exists($this->tempFilename)) {// On supprime le fichier
			unlink($this->tempFilename);
		}
	}

	/**
	 * Set mimeType
	 *
	 * @param string $mimeType
	 *
	 * @return File
	 */
	public function setMimeType($mimeType)
	{
		$this->mimeType = $mimeType;

		return $this;
	}

	/**
	 * Get mimeType
	 *
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->mimeType;
	}

	/**
	 * Set originalName
	 *
	 * @param string $originalName
	 *
	 * @return File
	 */
	public function setOriginalName($originalName)
	{
		$this->originalName = $originalName;

		return $this;
	}

	/**
	 * Get originalName
	 *
	 * @return string
	 */
	public function getOriginalName()
	{
		return $this->originalName;
	}
}
