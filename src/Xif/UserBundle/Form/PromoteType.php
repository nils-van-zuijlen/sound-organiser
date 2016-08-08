<?php
// src/Xif/UserBundle/Form/PromoteType.php

namespace Xif\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromoteType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add(
				'roleModo',
				CheckboxType::class,
				array('required' => false)
				)
			->add(
				'roleAdmin',
				CheckboxType::class,
				array('required' => false)
				)
			->add(
				'roleSuperAdmin',
				CheckboxType::class,
				array('required' => false)
				)
			->add(
				'roleAllowedToSwitch',
				CheckboxType::class,
				array('required' => false)
				)
			->add(
				'save',
				SubmitType::class
				)
			;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver
			->setDefaults(
				array(
					'data_class' => 'Xif\UserBundle\FormModels\Promote'
					)
				);
		}
}