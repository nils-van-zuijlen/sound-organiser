<?php

namespace Xif\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xif\CoreBundle\FormModels\ContactModel;
use Xif\CoreBundle\Form\ContactType;

/**
 * Controlleur du CoreBundle
 * Font la liaison entre les bundles
 */
class CoreController extends Controller {
	const NB_ACTUS_IN_INDEX = 5;

	/**
	 * Page d'accueil
	 * 
	 * @return Response
	 */
	public function indexAction() {
		$entityManager = $this->getDoctrine()->getManager();
		$actus = $entityManager->getRepository('XifCoreBundle:Actus')->getLasts(self::NB_ACTUS_IN_CAROUSEL);

		return $this->render(
			'XifCoreBundle:Core:index.html.twig',
			array(
				'actus' => $actus
				)
			);
	}

	/**
	 * Page d'affichage des projets publics
	 * 
	 * @param  int      $page N° de page demandée
	 * @return Response
	 */
	public function exploreAction($page, $admin) {
		// évite les visites non-souhaitées
		if (!$admin && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			throw $this->createNotFoundException('Fonction en cours d\'implémentation');
		}
		
		if ($page < 1) {
			throw $this->createNotFoundException('La page ' . $page . ' n\'existe pas.');
		}
		
		$entityManager = $this->getDoctrine()->getManager();

		$nbPerPage = $this->container->getParameter('nb_per_page');

		if ($admin && $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			$projects = $entityManager
				->getRepository('XifSoundOrganiserBundle:Project')
				->getPaginedProjects($page, $nbPerPage);
		} else {
			$projects = null;
		}

		$nbPages = ceil(count($projects) / $nbPerPage);

		if ($page != 1 && $page > $nbPages) {
			throw $this->createNotFoundException('La page ' . $page . ' n\'existe pas.');
		}

		return $this->render(
			'XifCoreBundle:Core:explore.html.twig',
			array(
				'projects' => $projects,
				'page'     => $page,
				'nbPages'  => $nbPages,
				'admin'    => $admin
				)
			);
	}

	public function creditsAction()
	{
		return $this->render('XifCoreBundle:Core:credits.html.twig');
	}

	public function conditionsAction()
	{
		return $this->render('XifCoreBundle:Core:conditions.html.twig');
	}

	public function helpAction()
	{
		return $this->render('XifCoreBundle:Core:help.html.twig');
	}

	public function contactAction(Request $request)
	{
		$toHydrate = new ContactModel();

		$form = $this->createForm(ContactType::class, $toHydrate);

		//requète POST
		if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
			
			$mail = new \Swift_Message();
			$toHydrate->done($mail);
			$mail->setTo($this->container->getParameter('mailer_user'));
			$mail->setSender($this->container->getParameter('mailer_user'));
			$this->get('mailer')->send($mail);

			$request->getSession()->getFlashBag()->add('info', 'Votre demande a bien été envoyée');

			return $this->redirectToRoute('xif_core_homepage');
		}

		return $this->render(
			'XifCoreBundle:Core:contact.html.twig',
			array(
				'form' => $form->createView()
				)
			);
	}
}
