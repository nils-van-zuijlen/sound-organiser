<?php
// src/Xif/CoreBundle/Form/Type/ActusType.php

namespace Xif\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActusType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add(
				'titre',
				TextType::class
				)
			->add(
				'contenu',
				TextareaType::class
				)
			->add(
				'submit',
				SubmitType::class,
				array(
					'label' => 'Publier',
					'attr'  => array(
						'class' => 'btn btn-primary'
						),
					)
				)
			;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver
			->setDefaults(
				array(
					'data_class' => 'Xif\CoreBundle\Entity\Actus'
					)
				);
		}
}
