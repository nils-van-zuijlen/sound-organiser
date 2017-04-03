<?php
// src/Xif/SoundOrganiserBundle/Controller/SoundOrganiserController.php

namespace Xif\SoundOrganiserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xif\SoundOrganiserBundle\Entity\Project;
use Xif\SoundOrganiserBundle\Entity\SongLine;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

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
			throw $this->createNotFoundException('Projet inexistant.');
		}

		if (!$this
				->getUser()
				->getId()
				== $project
					->getOwner()
					->getId()
			|| ($admin && !$this
				->get('security.authorization_checker')
				->isGranted('ROLE_ADMIN'))
			) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas le propriétaire du fichier');
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
				$this->getUser()
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
			throw $this->createNotFoundException('Projet inexistant.');
		}

		if (!$this->getUser()->getId() == $project->getOwner()->getId()) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas le propriétaire du fichier');
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
			throw new PreconditionFailedHttpException('Projet inexistant.');
		}
		if (!$this->getUser()->getId() == $project->getOwner()->getId()
			) {
			return $this->createAccessDeniedException('Vous n\'êtes pas le propriétaire du projet');
		}

		if ($request->isMethod('POST')) {
			$POST = $request->request;

			// son
			if ($POST->has('lineId')) {
				if (null === $songLine = $entityManager
					->getRepository('XifSoundOrganiserBundle:SongLine')
					->find( (int) $POST->get('lineId'))
					) {
					throw new PreconditionFailedHttpException('Son inexistant.');
				}
				if (!$songLine->getProject()->getId() == $project->getId()) {
					throw new PreconditionFailedHttpException('Le son n\'appartient pas au projet');
				}

				// fichier
				if ($POST->has('file')) {
					if (null === $file = $this
						->getDoctrine()
						->getManager()
						->getRepository('XifFileBundle:MultipleFile')
						->find( (int) $POST->get('file'))
						) {
						throw $this->createNotFoundException('Ligne inexistante.');
					}

					$songLine->setMultipleFile($file);
					$entityManager->flush();
					return new Response(null);
				}

				//transition
				if ($POST->has('trans1') && $POST->has('trans2') && $POST->has('type')) {
					$songLine
						->setType($POST->get('type'))
						->setTrans1($POST->get('trans1'))
						->setTrans2($POST->get('trans2'));
					$entityManager->flush();
					return new Response(null);
				}

				//nom
				if ($POST->has('songName')) {
					$songLine->setName((string) $POST->get('songName'));
					$entityManager->flush();
					return new Response(null);
				}

				//description
				if ($POST->has('songDescr')) {
					$songLine->setDescription((string) $POST->get('songDescr'));
					$entityManager->flush();
					return new Response(null);
				}

				//volume
				if ($POST->has('songVol')) {
					$songLine->setVol((float) $POST->get('songVol'));
					$entityManager->flush();
					return new Response(null);
				}
			} else {
				//titre du projet
				if ($POST->has('projTitle')) {
					$project->setTitle((string) $POST->get('projTitle'));
					$entityManager->flush();
					return new Response(null);
				}

				//description du projet
				if ($POST->has('projDescr')) {
					$project->setDescription((string) $POST->get('projDescr'));
					$entityManager->flush();
					return new Response(null);
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
					$response = new Response(json_encode($songLine, JSON_PRETTY_PRINT), Response::HTTP_CREATED);
					$response->headers->set('Content-Type', 'application/json');
					return $response;
				}

				//suppression d'un son
				if ($POST->has('removeSong')) {
					if (null === $songLine = $entityManager
						->getRepository('XifSoundOrganiserBundle:SongLine')
						->find( (int) $POST->get('removeSong'))
						) {
						throw new PreconditionFailedHttpException('Ligne inexistante.');
					}
					if (!$songLine->getProject()->getId() == $project->getId()) {
						throw new PreconditionFailedHttpException('N\'appartient pas au projet');
					}

					$entityManager->remove($songLine);
					$entityManager->flush();
					return new Response(null, Response::HTTP_OK);
				}
			}
		}

		throw $this->createNotFoundException('Mauvais paramètres POST');
	}

	public function deleteAction($id)
	{
		$entityManager = $this->getDoctrine()->getManager();

		if (null === $project = $entityManager->getRepository('XifSoundOrganiserBundle:Project')->find($id)) {
			throw $this->createNotFoundException('Ce projet n\'existe pas');
		}

		if (!$this->getUser()->getId()== $project->getOwner()->getId()) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas le propriétaire du fichier');
		}

		$entityManager->remove($project);
		$entityManager->flush();

		return $this->redirectToRoute('xif_soundorganiser_homepage');
	}
}
