<?php

namespace Xif\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Xif\CoreBundle\Entity\Actus;
use Xif\CoreBundle\Form\Type\ActusType;

class ActusController extends Controller
{
	public function viewOneAction(Request $request, $id)
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('XifCoreBundle:Actus');

		if (!($actu = $repository->find($id))) {
			throw new NotFoundHttpException("L'actu n'existe pas");
		}

		$number = $repository->countAll();

		return $this->render(
			'XifCoreBundle:Actus:viewOne.html.twig',
			array(
				'actu'   => $actu,
				'number' => $number
				)
			);
	}

	public function editAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();

		if (!($actu = $entityManager->getRepository('XifCoreBundle:Actus')->find($id))) {
			throw new NotFoundHttpException("L'actu n'existe pas");
		}

		$form = $this->createForm(ActusType::class, $actu);

		if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
			
			$entityManager->flush();

			$request->getSession()->getFlashBag()->add('info', 'L\'actu a bien été modifiée');

			return $this->redirectToRoute(
				'xif_core_viewactu',
				array(
					'id' => $id
					)
				);
		}

		return $this->render(
			'XifCoreBundle:Actus:edit.html.twig',
			array(
				'form' => $form->createView()
				)
			);
	}

	public function newAction(Request $request)
	{
		$entityManager = $this
			->getDoctrine()
			->getManager();

		$actu = new Actus();

		$actu->setAuteur($this->getUser());

		$form = $this->createForm(ActusType::class, $actu);

		if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
			
			$entityManager->persist($actu);
			$entityManager->flush();

			$request->getSession()->getFlashBag()->add('info', 'L\'actu a bien été ajoutée, elle est affichée sur la page d\'accueil');

			return $this->redirectToRoute(
				'xif_core_viewactu',
				array(
					'id' => $actu->getId()
					)
				);
		}

		return $this->render(
			'XifCoreBundle:Actus:new.html.twig',
			array(
				'form' => $form->createView()
				)
			);
	}
}
