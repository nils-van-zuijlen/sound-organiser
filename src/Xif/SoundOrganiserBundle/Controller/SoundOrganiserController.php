<?php
// src/Xif/SoundOrganiserBundle/Controller/SoundOrganiserController.php

namespace Xif\SoundOrganiserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xif\SoundOrganiserBundle\Entity\Project;
use Xif\SoundOrganiserBundle\Entity\SongLine;
use Xif\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controlleurs principaux de SoundOrganiserBundle
 */
class SoundOrganiserController extends Controller {
	
	/**
	 * Page d'accueil du bundle
	 * Affiche les projets de l'utilisateur
	 *
	 * @return Response
	 */
	public function indexAction()
	{
		$entityManager = $this->getDoctrine()->getManager();
		$user = $this->get('security.token_storage')->getToken()->getUser();

		$projects = $user->getProjects();

		return $this->render(
			'XifSoundOrganiserBundle:SoundOrganiser:index.html.twig',
			array(
				'projects' => $projects
				)
			);
	}

	/**
	 * Page de lecture de projet
	 * 
	 * @param  integer   $id  Id du projet à lire
	 * @return Response
	 */
	public function playAction($id, $admin = false)
	{
		if (null === $project = $this
			->getDoctrine()
			->getManager()
			->getRepository('XifSoundOrganiserBundle:Project')
			->find($id)
			) {
			throw new NotFoundHttpException('Projet inexistant.');
		}

		if (!$this
				->get('security.token_storage')
				->getToken()
				->getUser()
				->getId()
				== $project
					->getOwner()
					->getId()
			|| ($admin && !$this
				->get('security.authorization_checker')
				->isGranted('ROLE_ADMIN'))
			) {
			throw new AccessDeniedException('Vous n\'êtes pas le propriétaire du fichier');
		}

		return $this->render(
			'XifSoundOrganiserBundle:SoundOrganiser:playPage.html.twig',
			array(
				'project' => $project
				)
			);
	}

	public function newAction()
	{
		$entityManager = $this->getDoctrine()->getManager();

		$songLine = new SongLine;
		$songLine
			->setName('Titre du son')
			->setDescription('Description du son')
			->setType('1')
			->setTrans2('s')
			->setVol(0.7);

		$project = new Project();
		$project
			->setTitle('Le titre du projet')
			->setDescription('La description du projet')
			->setOwner(
				$this
					->get('security.token_storage')
					->getToken()
					->getUser()
				)
			->addSongLine($songLine);

		$entityManager->persist($project);
		$entityManager->flush();
		
		return $this->redirectToRoute(
			'xif_soundorganiser_edit',
			array(
				'id' => $project->getId()
				)
			);
	}

	public function editAction($id)
	{
		if (null === $project = $this
			->getDoctrine()
			->getManager()
			->getRepository('XifSoundOrganiserBundle:Project')
			->find($id)
			) {
			throw new NotFoundHttpException('Projet inexistant.');
		}

		if (!$this
				->get('security.token_storage')
				->getToken()
				->getUser()
				->getId()
				== $project
					->getOwner()
					->getId()
			) {
			throw new AccessDeniedException('Vous n\'êtes pas le propriétaire du fichier');
		}

		return $this->render(
			'XifSoundOrganiserBundle:SoundOrganiser:edit.html.twig',
			array(
				'project' => $project
				)
			);
	}

	public function editAjaxAction(Request $request, $projId)
	{
		$entityManager = $this->getDoctrine()->getManager();

		if (null === $project = $entityManager
			->getRepository('XifSoundOrganiserBundle:Project')
			->find($projId)
			) {
			return new Response(
				'Projet inexistant.',
				Response::HTTP_PRECONDITION_FAILED
				);
		}
		if (!$this
				->get('security.token_storage')
				->getToken()
				->getUser()
				->getId()
				== $project
					->getOwner()
					->getId()
			) {
			return new Response(
				'Vous n\'êtes pas le propriétaire du projet',
				Response::HTTP_UNAUTHORIZED
				);
		}

		if ($request->isMethod('POST')) {
			$POST = $request->request;

			// son
			if ($POST->has('lineId')) {
				if (null === $songLine = $entityManager
					->getRepository('XifSoundOrganiserBundle:SongLine')
					->find( (int) $POST->get('lineId'))
					) {
					return new Response(
						'Son inexistant.',
						Response::HTTP_PRECONDITION_FAILED
						);
				}
				if (!$songLine->getProject()->getId() == $project->getId()) {
					return new Response(
						'Le son n\'appartient pas au projet',
						Response::HTTP_PRECONDITION_FAILED
						);
				}

				// fichier
				if ($POST->has('file')) {
					if (null === $file = $this
						->getDoctrine()
						->getManager()
						->getRepository('XifFileBundle:File')
						->find( (int) $POST->get('file'))
						) {
						throw new NotFoundHttpException('Ligne inexistante.');
					}

					$songLine->setFile($file);
					$entityManager->flush();
					return new Response(null, Response::HTTP_ACCEPTED);
				}

				//transition
				if ($POST->has('trans1') && $POST->has('trans2') && $POST->has('type')) {
					$songLine
						->setType($POST->get('type'))
						->setTrans1($POST->get('trans1'))
						->setTrans2($POST->get('trans2'));
					$entityManager->flush();
					return new Response(null, Response::HTTP_ACCEPTED);
				}

				//nom
				if ($POST->has('songName')) {
					$songLine->setName((string) $POST->get('songName'));
					$entityManager->flush();
					return new Response(null, Response::HTTP_ACCEPTED);
				}

				//description
				if ($POST->has('songDescr')) {
					$songLine->setDescription((string) $POST->get('songDescr'));
					$entityManager->flush();
					return new Response(null, Response::HTTP_ACCEPTED);
				}

				//volume
				if ($POST->has('songVol')) {
					$songLine->setVol((float) $POST->get('songVol'));
					$entityManager->flush();
					return new Response(null, Response::HTTP_ACCEPTED);
				}
			} else {
				//titre du projet
				if ($POST->has('projTitle')) {
					$project->setTitle((string) $POST->get('projTitle'));
					$entityManager->flush();
					return new Response(null, Response::HTTP_ACCEPTED);
				}

				//description du projet
				if ($POST->has('projDescr')) {
					$project->setDescription((string) $POST->get('projDescr'));
					$entityManager->flush();
					return new Response(null, Response::HTTP_ACCEPTED);
				}

				//nouveau son
				if ($POST->get('addSong') == 'true') {
					$songLine = new SongLine();
					$songLine
						->setName('Titre du son')
						->setDescription('Description du son')
						->setType('1')
						->setTrans2('s')
						->setVol(0.7);
					$project->addSongLine($songLine);
					$entityManager->flush();
					$response = new Response($songLine->getJson(), Response::HTTP_CREATED);
					$response->headers->set('Content-Type', 'application/json');
					return $response;
				}

				if ($POST->has('removeSong')) {
					if (null === $songLine = $entityManager
						->getRepository('XifSoundOrganiserBundle:SongLine')
						->find( (int) $POST->get('removeSong'))
						) {
						return new Response(
							'Ligne inexistante.',
							Response::HTTP_PRECONDITION_FAILED
							);
					}
					if (!$songLine->getProject()->getId() == $project->getId()) {
						return new Response(
							'N\'appartient pas au projet',
							Response::HTTP_PRECONDITION_FAILED
							);
					}

					$entityManager->remove($songLine);
					$entityManager->flush();
					return new Response(null, Response::HTTP_OK);
				}
			}
		}

		throw new NotFoundHttpException('Mauvais paramètres POST');
	}

	public function deleteAction($id)
	{
		$entityManager = $this
			->getDoctrine()
			->getManager();

		if (null === $project = $entityManager->getRepository('XifSoundOrganiserBundle:Project')->find($id)) {
			throw new NotFoundHttpException('Ce projet n\'existe pas');
		}

		if (!$this
				->get('security.token_storage')
				->getToken()
				->getUser()
				->getId()
				== $project
					->getOwner()
					->getId()
			) {
			throw new AccessDeniedHttpException('Vous n\'êtes pas le propriétaire du fichier');
		}

		$entityManager->remove($project);
		$entityManager->flush();

		return $this->redirectToRoute('xif_soundorganiser_homepage');
	}
}