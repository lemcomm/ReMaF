<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for updating entity descriptions.
 *
 * Accepts the following options (in their legacy order):
 * * 'text' - string (null) - type of action
 */
class DescriptionNewType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'newdescription_95315',
			'translation_domain' => 'actions',
			'text'			=> null,
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$text = $options['text'];
		$builder->add('text', TextareaType::class, array(
			'label'=>'control.description.full',
			'data'=>$text,
			'required'=>true,
		));
	}
}
