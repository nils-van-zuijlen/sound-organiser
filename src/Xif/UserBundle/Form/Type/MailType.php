<?php
// src/Xif/UserBundle/Form/Type/MailType.php

namespace Xif\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add(
				'subject',
				TextType::class
				)
			->add(
				'mailContent',
				TextareaType::class
				)
			->add(
				'send',
				SubmitType::class,
				array(
					'attr' => array(
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
					'data_class' => 'Xif\UserBundle\FormModels\Mail'
					)
				);
		}
}
