<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AreYouSureType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'doublecheck_159753',
			'translation_domain' => 'settings'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('sure', CheckboxType::class, array(
			'label' => 'areyousure',
			'required' => true
		));

		$builder->add('submit', SubmitType::class, array('label'=>'button.submit'));
	}
}
