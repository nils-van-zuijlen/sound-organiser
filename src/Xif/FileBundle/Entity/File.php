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
	 * @ORM\ManyToOne(targetEntity="Xif\FileBundle\Entity\MultipleFile", inversedBy="files", cascade="persist")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $group;

	/**
	 * Used in the delete process.
	 * It stores the filename in order to delete it once the request is terminated.
	 * @var string
	 */
	protected $tempFilename;


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
	 * Set group
	 *
	 * @param \Xif\FileBundle\Entity\MultipleFile $group
	 *
	 * @return File
	 */
	public function setGroup(\Xif\FileBundle\Entity\MultipleFile $group)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * Get group
	 *
	 * @return \Xif\FileBundle\Entity\MultipleFile
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Méthodes pour l'upload de fichiers
	 */

	public function getLocation()
	{
		return MultipleFile::UPLOAD_ROOT_DIR . $this->group->getId() . '.' . $this->extension;
	}

	public function preUpload(UploadedFile $file)
	{
		$this->extension = $file->guessExtension();
		$this->mimeType  = $file->getMimeType();
	}

	public function upload(UploadedFile $file)
	{
		$file->move(
			MultipleFile::UPLOAD_ROOT_DIR,
			$this->group->getId() . '.' . $this->extension
			);
		print('"'.$this->group->getId().'"');
	}

	/**
	 * @ORM\PreRemove()
	 */
	public function preRemoveUpload()
	{
		// On sauvegarde temporairement le nom du fichier, car il dépend de l'id
		$this->tempFilename = $this->getLocation();
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
}
