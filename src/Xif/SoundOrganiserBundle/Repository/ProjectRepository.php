<?php

namespace Xif\SoundOrganiserBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityRepository;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends EntityRepository {
	public function getAllProjects($page, $nbPerPage)
	{
		$queryBuilder = $this->createQueryBuilder('p');

		$queryBuilder
			->innerJoin('p.owner', 'o')
			->addSelect('o');

		$queryBuilder
			// On définit l'annonce à partir de laquelle commencer la liste
			->setFirstResult(($page-1) * $nbPerPage)
			// Ainsi que le nombre d'annonce à afficher sur une page
			->setMaxResults($nbPerPage);

		return new Paginator($queryBuilder);
	}
}
