<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/*
 * Form for confirming user entry.
 *
 * Accepts the following options (in their legacy order):
 * * 'translation_domain' - string - Translation domain for the form. Defaults to 'settings'
 * * 'label' - string - Checbox translation label, defaults to 'areyousure'
 * * 'submit' - string - Submit button translation label, defaults to 'button.submit'
 */
class AreYouSureType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'doublecheck_159753',
			'translation_domain' => 'settings',
			'label' => 'areyousure',
			'submit' => 'button.submit'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('sure', CheckboxType::class, array(
			'label' => $options['label'],
			'required' => true
		));

		$builder->add('submit', SubmitType::class, array('label'=>$options['submit']));
	}
}
