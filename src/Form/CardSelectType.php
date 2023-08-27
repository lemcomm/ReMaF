<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardSelectType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'cardselect_136',
			'translation_domain' => 'dungeons'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('card', NumberType::class, array(
			'label' => false,
			'required' => true,
		));
	}

}
