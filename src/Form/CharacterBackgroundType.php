<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterBackgroundType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'background_1651345',
			'translation_domain'	=> 'actions',
			'alive'			=> true
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('appearance', TextareaType::class, array(
			'label'=>'meta.background.appearance',
			'trim'=>true,
			'required'=>false
		));
		$builder->add('personality', TextareaType::class, array(
			'label'=>'meta.background.personality',
			'trim'=>true,
			'required'=>false
		));
		$builder->add('secrets', TextareaType::class, array(
			'label'=>'meta.background.secrets',
			'trim'=>true,
			'required'=>false
		));

		if (!$options['alive']) {
			$builder->add('death', TextareaType::class, array(
				'label'=>'meta.background.death',
				'trim'=>true,
				'required'=>false
			));			
		}

		$builder->add('submit', SubmitType::class, array('label'=>'meta.background.submit'));
	}
}
