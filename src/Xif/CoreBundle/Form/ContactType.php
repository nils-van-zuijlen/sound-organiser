<?php
// src/Xif/CoreBundle/Form/ContactType.php

namespace Xif\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add(
				'senderName',
				TextType::class,
				array(
					'attr' => array(
						'minlength' => 3,
						),
					)
				)
			->add(
				'senderMail',
				EmailType::class
				)
			->add(
				'subject',
				TextType::class,
				array(
					'attr' => array(
						'minlength' => 3,
						'maxlength' => 255,
						),
					)
				)
			->add(
				'body',
				TextareaType::class,
				array(
					'attr' => array(
						'minlength' => 10,
						),
					)
				)
			->add(
				'send',
				SubmitType::class,
				array(
					'label' => 'Envoyer',
					'attr'  => array(
						'class' => 'btn btn-primary',
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
					'data_class' => 'Xif\CoreBundle\FormModels\ContactModel'
					)
				);
		}
}
