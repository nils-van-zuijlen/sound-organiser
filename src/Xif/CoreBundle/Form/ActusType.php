<?php
// src/Xif/CoreBundle/Form/ActusType.php

namespace Xif\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
				array('label' => 'Publier')
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