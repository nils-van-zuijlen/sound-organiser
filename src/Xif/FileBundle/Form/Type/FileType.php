<?php
// src/Xif/FileBundle/Form/Type/FileType.php

namespace Xif\FileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType as BaseFileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('file', BaseFileType::class)
			->add('send', SubmitType::class);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver
			->setDefaults(
				array(
					'data_class' => 'Xif\FileBundle\Entity\File'
					)
				);
		}
}
