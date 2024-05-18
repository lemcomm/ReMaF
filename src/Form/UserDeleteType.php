<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

/*
 * Form for confirming user entry.
 *
 * Accepts the following options (in their legacy order):
 * * 'translation_domain' - string - Translation domain for the form. Defaults to 'settings'
 * * 'label' - string - Checbox translation label, defaults to 'areyousure'
 * * 'submit' - string - Submit button translation label, defaults to 'button.submit'
 */
class UserDeleteType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'userdelete_14728',
			'translation_domain' => 'core',
			'label' => 'areyousure',
			'submit' => 'button.submit'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('email', EmailType::class, [
			'label' => 'form.delete.email',
			'required' => false,
			'attr' => [
				'autocomplete' => 'off',
			],
			'constraints' => [
				new Email([
					'message' => 'form.email.help',
				]),
			],
		]);
		$builder->add('sure', CheckboxType::class, array(
			'label' => 'form.delete.confirm',
			'required' => true
		));

		$builder->add('submit', SubmitType::class, array('label'=>'form.delete.submit'));
	}
}
