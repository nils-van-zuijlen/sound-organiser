<?php

namespace Xif\UserBundle\FormModels;

use Xif\UserBundle\Entity\User;

class Promote
{
	protected $roleModo;
	protected $roleAdmin;
	protected $roleSuperAdmin;
	protected $roleAllowedToSwitch;
	protected $user;

	public function setRoleAdmin($roleAdmin)
	{
		$this->roleAdmin = (bool) $roleAdmin;
		return $this;
	}
	public function getRoleAdmin()
	{
		return $this->roleAdmin;
	}

	public function setRoleSuperAdmin($roleSuperAdmin)
	{
		$this->roleSuperAdmin = (bool) $roleSuperAdmin;
		return $this;
	}
	public function getRoleSuperAdmin()
	{
		return $this->roleSuperAdmin;
	}

	public function setRoleAllowedToSwitch($roleAllowedToSwitch)
	{
		$this->roleAllowedToSwitch = (bool) $roleAllowedToSwitch;
		return $this;
	}
	public function getRoleAllowedToSwitch()
	{
		return $this->roleAllowedToSwitch;
	}

	public function setRoleModo($roleModo)
	{
		$this->roleModo = (bool) $roleModo;
		return $this;
	}
	public function getRoleModo()
	{
		return $this->roleModo;
	}

	protected function setRolesFromUser()
	{
		$roles = $this->user->getRoles();
		$this->roleAdmin = (in_array('ROLE_ADMIN', $roles)) ? true : false ;
		$this->roleSuperAdmin = (in_array('ROLE_SUPER_ADMIN', $roles)) ? true : false ;
		$this->roleAllowedToSwitch = (in_array('ROLE_ALLOWED_TO_SWITCH', $roles)) ? true : false ;
		$this->roleModo = (in_array('ROLE_MODO', $roles)) ? true : false ;
	}

	public function __construct(User $user)
	{
		$this->user = $user;
		$this->setRolesFromUser();
	}

	public function done()
	{
		$user = $this->user;
		if ($this->roleAdmin) {
			$user->addRole('ROLE_ADMIN');
		} else {
			$user->removeRole('ROLE_ADMIN');
		}
		if ($this->roleSuperAdmin) {
			$user->addRole('ROLE_SUPER_ADMIN');
		} else {
			$user->removeRole('ROLE_SUPER_ADMIN');
		}
		if ($this->roleAllowedToSwitch) {
			$user->addRole('ROLE_ALLOWED_TO_SWITCH');
		} else {
			$user->removeRole('ROLE_ALLOWED_TO_SWITCH');
		}
		if ($this->roleModo) {
			$user->addRole('ROLE_MODO');
		} else {
			$user->removeRole('ROLE_MODO');
		}
	}
}