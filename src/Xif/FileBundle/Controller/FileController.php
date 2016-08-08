<?php
// src/Xif/FileBundle/Controller/FileController.php

namespace Xif\FileBundle\Controller;

// controleur de base
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// composants de requète / réponse HTTP
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
// gestion de la sécurité
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
// classes ORM du bundle
use Xif\FileBundle\Entity\File;
// classes ORM de bundles externes
use Xif\UserBundle\Entity\User;
use Xif\SoundOrganiserBundle\Entity\Project;
// classes formulaires du bundle
use Xif\FileBundle\Form\FileType;

/**
* Gestion des fichiers de son
*/
class FileController extends Controller {
	
	/**
	 * Récupérer un fichier enregistré en BDD
	 * 
	 * @param  integer $id      Id du fichier demandé
	 * @return Response         Fichier extrait
	 */
	public function getFileAction($id, $admin)
	{
		$entityManager = $this->getDoctrine()->getManager();

		if (null === $fileObject = $entityManager
				->getRepository('XifFileBundle:File')
				->find($id)
				) {
			throw new NotFoundHttpException('Fichier inexistant');	
		}

		//Is owned by querier
		if (
			!$this
				->get('security.token_storage')
				->getToken()
				->getUser()
				->getId()
				== $fileObject
					->getOwner()
					->getId()
			&& !$admin && !$this
				->get('security.authorization_checker')
				->isGranted('ROLE_ADMIN')
					) {
			throw new AccessDeniedException('Vous n\'êtes pas le propriétaire du fichier');
		}

		$response = new Response();
		$response->setContent(
			file_get_contents(
				$fileObject->getLocation()
				)
			);
		$response->headers->set('Content-Type', $fileObject->getMimeType());
		$response->headers->set('Content-Disposition', 'filename="' . $fileObject->getOriginalName() . '"');

		return $response;
	}

	/**
	 * Ajouter un fichier à ses projets
	 *
	 * @param Request $request Requète de l'utilisateur
	 */
	public function addFileAction(Request $request)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$file          = new File(
			$this->get('security.token_storage')->getToken()->getUser()
			);

		$form          = $this
							->get('form.factory')
							->create(FileType::class, $file);

		if (
			$request->isMethod('POST') 
			&& $form->handleRequest($request)->isValid()
			) {
			
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($file);
			$entityManager->flush();

			return new Response('<!DOCTYPE html><html><body><script>window.top.window.chooseMyFiles();</script></body></html>', 200);
		}

		return $this->render(
			'XifFileBundle:File:addFile.html.twig',
			array(
				'form' => $form->createView()
				)
			);
	}

	public function removeFileAction($id, $admin)
	{
		$entityManager = $this
			->getDoctrine()
			->getManager();
		if(null == $file = $entityManager->getRepository('XifFileBundle:File')->find($id)) {
			throw new NotFoundHttpException('Fichier inexistant');
		}

		// utilisateur === propriétaire
		if (!$this->get('security.token_storage')->getToken()->getUser()->getId()== $file->getOwner()->getId()
			|| ($admin && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN'))) {
			throw new AccessDeniedException('Vous n\'êtes pas le propriétaire du fichier.');
		}

		// suppression du fichier
		$entityManager->remove($file);
		$entityManager->flush();

		if ($admin && $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			return $this->redirectToRoute('xif_user_viewfiles');
		}

		return new Response('File deleted');
	}

	public function listAction()
	{
		// l'utilisateur connecté est administrateur
		if (
			!$this
				->get('security.authorization_checker')
				->isGranted('ROLE_ADMIN')
				) {
			throw new AccessDeniedException('Veuillez vous connecter en tant qu\'administrateur.');
		}

		$entityManager = $this
			->getDoctrine()
			->getManager();
		$files = $entityManager
			->getRepository('XifFileBundle:File')
			->findAll();

		return $this->render(
			'XifUserBundle:Admin:listFiles.html.twig',
			array(
				'files' => $files,
				)
			);
	}

	public function getMineAction(Request $request)
	{
		$entityManager = $this
			->getDoctrine()
			->getManager();
		$files = $entityManager
			->getRepository('XifFileBundle:File')
			->findByOwner(
				$this
					->get('security.token_storage')
					->getToken()
					->getUser()
				);

		$filesArray = array();
		foreach ($files as $file) {
			$filesArray[] = array(
					'id' => $file->getId(),
					'name' => $file->getOriginalName()
				);
		}

		return new JsonResponse($filesArray);
	}
}