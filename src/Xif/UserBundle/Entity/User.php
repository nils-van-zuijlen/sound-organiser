<?php
// src/Xif/UserBundle/Entity/User.php

namespace Xif\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Table(name="xif_user")
 * @ORM\Entity(repositoryClass="Xif\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser {
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToMany(targetEntity="Xif\SoundOrganiserBundle\Entity\Project", mappedBy="owner", cascade={"persist", "remove"})
	 */
	protected $projects;

	/**
	 * @ORM\OneToMany(targetEntity="Xif\FileBundle\Entity\File", mappedBy="owner", cascade="remove")
	 */
	protected $files;


	public function __construct() {
		parent::__construct();
		// your own logic
	}

    /**
     * Add project
     *
     * @param \Xif\SoundOrganiserBundle\Entity\Project $project
     *
     * @return User
     */
    public function addProject(\Xif\SoundOrganiserBundle\Entity\Project $project)
    {
        $this->projects[] = $project;

        return $this;
    }

    /**
     * Remove project
     *
     * @param \Xif\SoundOrganiserBundle\Entity\Project $project
     */
    public function removeProject(\Xif\SoundOrganiserBundle\Entity\Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add file
     *
     * @param \Xif\FileBundle\Entity\File $file
     *
     * @return User
     */
    public function addFile(\Xif\FileBundle\Entity\File $file)
    {
        $this->files[] = $file;

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
}
