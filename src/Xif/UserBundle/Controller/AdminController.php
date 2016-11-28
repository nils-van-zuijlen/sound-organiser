<?php

namespace Xif\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Xif\UserBundle\FormModels\Promote;
use Xif\UserBundle\Form\Type\PromoteType;
use Xif\UserBundle\FormModels\Mail;
use Xif\UserBundle\Form\Type\MailType;
use Xif\CoreBundle\Entity\Actus;
use Xif\CoreBundle\Form\Type\ActusType;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class AdminController extends Controller
{
	public function indexAction(Request $request) {
		return $this->render('XifUserBundle:Admin:index.html.twig');
	}

	public function userListAction(Request $request)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$users = $entityManager->getRepository('XifUserBundle:User')->findAll();

		return $this->render(
			'XifUserBundle:Admin:userList.html.twig',
			array(
				'users' => $users
				)
			);
	}

	public function promoteAction(Request $request, $id)
	{
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
			throw new AccessDeniedException('Vous ne pouvez pas modifier les droits des utilisateurs');
		}

		$entityManager = $this
			->getDoctrine()
			->getManager();

		$user = $entityManager
			->getRepository('XifUserBundle:User')
			->find($id);

		if ($user === null) {
			$request->getSession()->getFlashBag()->add('info', 'Cet utilisateur n\'existe pas');
			return $this->redirectToRoute('xif_user_viewusers');
		}

		$toHydrate = new Promote($user);

		$form = $this->createForm(PromoteType::class, $toHydrate);

		//requète POST
		if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
			$toHydrate->done();

			$entityManager->flush();

			$request->getSession()->getFlashBag()->add('info', 'Utilisateur modifié.');

			return $this->redirectToRoute('xif_user_viewusers');
		}

		return $this->render(
			'XifUserBundle:Admin:promote.html.twig',
			array(
				'form' => $form->createView(),
				'user' => $user
				)
			);
	}

	public function deleteUserAction(Request $request, $id)
	{
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
			throw new AccessDeniedException('Vous ne pouvez pas supprimer un utilisateur');
		}

		$entityManager = $this
			->getDoctrine()
			->getManager();

		$user = $entityManager
			->getRepository('XifUserBundle:User')
			->find($id);

		if ($user === null) {
			$request->getSession()->getFlashBag()->add('info', 'Cet utilisateur n\'existe pas');
			return $this->redirectToRoute('xif_user_viewusers');
		}

		$entityManager->remove($user);
		$entityManager->flush();

		$request->getSession()->getFlashBag()->add('info', 'Cet utilisateur a été supprimé.');

		return $this->redirectToRoute('xif_user_viewusers');
	}

	public function lockAction(Request $request, $id)
	{
		$entityManager = $this
			->getDoctrine()
			->getManager();

		$user = $entityManager
			->getRepository('XifUserBundle:User')
			->find($id);

		if ($user === null) {
			$request->getSession()->getFlashBag()->add('info', 'Cet utilisateur n\'existe pas');
			return $this->redirectToRoute('xif_user_viewusers');
		}

		$user->setLocked($user->isAccountNonLocked());

		$entityManager->flush();

		if ($user->isLocked()) {
			$request->getSession()->getFlashBag()->add('info', 'Le compte a été bloqué');
		} else {
			$request->getSession()->getFlashBag()->add('info', 'Le compte a été débloqué');
		}

		return $this->redirectToRoute('xif_user_viewusers');
	}

	public function mailAction(Request $request, $id)
	{
		$entityManager = $this
			->getDoctrine()
			->getManager();

		$user = $entityManager
			->getRepository('XifUserBundle:User')
			->find($id);

		if ($user === null) {
			$request->getSession()->getFlashBag()->add('info', 'Cet utilisateur n\'existe pas');
			return $this->redirectToRoute('xif_user_viewusers');
		}

		$toHydrate = new Mail($user);

		$form = $this->createForm(MailType::class, $toHydrate);

		//requète POST
		if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
			
			$mail = new \Swift_Message();
			$body = $toHydrate->done($mail);
			$mail->setFrom($this->container->getParameter('mailer_user'));
			$mail->setBody($this->get('templating')->render('XifUserBundle:Mail:mail.html.twig', array('body' => $body)), 'text/html');
			$mail->addPart($this->get('templating')->render('XifUserBundle:Mail:mail.txt.twig', array('body' => strip_tags($body))), 'text/plain');
			$this->get('mailer')->send($mail);
			
			$request->getSession()->getFlashBag()->add('info', 'Le mail a été envoyé');

			return $this->redirectToRoute('xif_user_viewusers');
		}

		return $this->render(
			'XifUserBundle:Admin:mail.html.twig',
			array(
				'form' => $form->createView(),
				'user' => $user
				)
			);
	}
}
