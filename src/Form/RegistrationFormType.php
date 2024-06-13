<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType {

        public function configureOptions(OptionsResolver $resolver) {
                $resolver->setDefaults([
                        'data_class' => User::class,
                        'translation_domain' => 'core',
			'labels' => true
                ]);
        }

        public function buildForm(FormBuilderInterface $builder, array $options) {
                $builder->add('_username', TextType::class, [
			'label' => $options['labels']?'form.username.username':false,
			'constraints' => [
				new Regex([
					'pattern' => '/^[a-zA-Z0-9 \-_]*$/',
					'message' => 'form.username.help',
				]),
			],
			'attr' => [
				'placeholder' => 'form.username.username'
			]
		]);
                $builder->add('display_name', TextType::class, [
			'label' => $options['labels']?'form.register.display':false,
			'constraints' => [
				new Regex([
					'pattern' => '/^[a-zA-Z0-9 \-_]*$/',
					'message' => 'form.register.displayhelp',
				]),
			],
			'attr' => [
				'placeholder' => 'form.display.display'
			]
		]);
                $builder->add('email', TextType::class, [
			'label' => $options['labels']?'form.email.email':false,
			'constraints' => [
				new Email([
					'message' => 'form.email.help',
				]),
			],
			'attr' => [
				'placeholder' => 'form.email.email'
			]
		]);
                $builder->add('plainPassword', RepeatedType::class, [
			'type' => PasswordType::class,
			# instead of being set onto the object directly,
			# this is read and encoded in the controller
			'mapped' => false,
			'options' => ['attr' => ['password-field']],
			'required' => true,
			'invalid_message' => 'form.password.nomatch',
			'first_options' => [
				'label' => $options['labels']?'form.password.password':false,
				'attr' => [
					'placeholder' => 'form.password.password'
				]
			],
			'second_options' => [
				'label' => $options['labels']?'form.password.confirm':false,
				'attr' => [
					'placeholder' => 'form.password.confirm'
				]],
			'constraints' => [
				new Length([
					'min' => 8,
					'minMessage' => 'form.password.help',
					# max length allowed by Symfony for security reasons
					'max' => 4096,
				]),
			],
		]);
		$builder->add('agreeTerms', CheckboxType::class, [
			'label' => 'form.register.terms',
			'mapped' => false,
			'constraints' => [
				new IsTrue([
					'message' => 'form.register.toshelp',
				]),
			],
		]);
		$builder->add('newsletter', CheckboxType::class, [
			'label' => 'form.newsletter.newsletter',
			'mapped' => false,
			'requires' => false,
		]);
                $builder->add('submit', SubmitType::class, [
                        'label' => 'form.register.submit'
                ]);
        }
}
