<?php
// src/Xif/FileBundle/Controller/FileController.php

namespace Xif\FileBundle\Controller;

// controleur de base
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// composants de requète / réponse HTTP
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
// gestion de la sécurité
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
// classes ORM du bundle
use Xif\FileBundle\Entity\File;
use Xif\FileBundle\Entity\MultipleFile;
// classes ORM de bundles externes
use Xif\UserBundle\Entity\User;
use Xif\SoundOrganiserBundle\Entity\Project;
// classes formulaires du bundle
use Xif\FileBundle\Form\Type\FileType;

/**
* Gestion des fichiers de son
*/
class FileController extends Controller {
	
	/**
	 * Récupérer un fichier enregistré en BDD
	 * 
	 * @param  integer $id      Id du fichier demandé
	 * @param  boolean $admin   Mode administrateur
	 * @return Response         Fichier extrait
	 */
	public function getFileAction($id, $admin)
	{
		$em = $this->getDoctrine()->getManager();

		if (null === $fileObject = $em
				->getRepository('XifFileBundle:File')
				->getWithGroupById($id)
				) {
			throw $this->createNotFoundException('Fichier inexistant en BDD');	
		}
		if (!file_exists($fileObject->getLocation()))
			throw $this->createNotFoundException('Fichier inexistant dans le système de fichiers');

		//Is owned by querier
		if (
			!$this
				->getUser()
				->getId()
				== $fileObject
					->getGroup()
					->getOwner()
					->getId()
			&& !$admin && !$this
				->get('security.authorization_checker')
				->isGranted('ROLE_ADMIN')
					) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas le propriétaire du fichier');
		}

		$response = new BinaryFileResponse($fileObject->getLocation());
		/*$response->setContent(
			file_get_contents(
				$fileObject->getLocation()
				)
			);
		$response->headers->set('Content-Type', $fileObject->getMimeType());*/
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $fileObject->getGroup()->getFilename());

		return $response;
	}

	/**
	 * Ajouter un fichier à ses projets
	 *
	 * @param Request $request Requète de l'utilisateur
	 */
	public function addFileAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$file = new MultipleFile($this->getUser());

		$form = $this->get('form.factory')->create(FileType::class, $file);

		if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($file);
			$em->flush();

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
		$em = $this->getDoctrine()->getManager();

		if(null === $file = $em->getRepository('XifFileBundle:MultipleFile')->getWithFilesById($id)) {
			throw $this->createNotFoundException('Fichier inexistant');
		}

		// utilisateur === propriétaire
		if (!$this->getUser()->getId()== $file->getOwner()->getId()
			|| ($admin && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN'))) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas le propriétaire du fichier.');
		}

		// suppression du fichier
		$em->remove($file);
		$em->flush();

		if ($admin && $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			return $this->redirectToRoute('xif_user_viewfiles');
		}

		return new Response('File deleted');
	}

	public function listAction()
	{
		// l'utilisateur connecté est administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Veuillez vous connecter en tant qu\'administrateur.');

		$files = $this
			->getDoctrine()
			->getManager()
			->getRepository('XifFileBundle:MultipleFile')
			->getAllWithFiles();

		return $this->render(
			'XifUserBundle:Admin:listFiles.html.twig',
			array(
				'files' => $files,
				)
			);
	}

	public function getMineAction(Request $request)
	{
		$files = $this
			->getDoctrine()
			->getManager()
			->getRepository('XifFileBundle:MultipleFile')
			->findByOwner(
				$this->getUser()
				);

		$filesArray = array();
		foreach ($files as $file) {
			$filesArray[] = array(
					'id' => $file->getId(),
					'name' => $file->getFilename()
				);
		}

		return new JsonResponse($filesArray);
	}

	public function getNameAction($id)
	{
		$file = $this->getDoctrine()->getManager()->getRepository('XifFileBundle:MultipleFile')->find($id);
		if ($file === null)
			throw $this->createNotFoundException('The multifile of id "'.$id.'" does not exist in database.');
		return new Response($file->getFilename());
	}

	public function audioSourcesAction($id)
	{
		$multifile = $this->getDoctrine()->getManager()->getRepository('XifFileBundle:MultipleFile')->getWithFilesById($id);
		if ($multifile === null)
			throw $this->createNotFoundException('Le groupe de fichiers n°'.$id.' n\'existe pas.');

		return $this->render(
			'XifFileBundle:File:audioSources.html.twig',
			array(
				'group' => $multifile,
		));
	}
}
