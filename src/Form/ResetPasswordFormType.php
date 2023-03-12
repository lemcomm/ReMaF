<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordFormType extends AbstractType {

        public function configureOptions(OptionsResolver $resolver) {
                $resolver->setDefaults([
                        'translation_domain' => 'core'
                ]);
        }

        public function buildForm(FormBuilderInterface $builder, array $options) {
                $builder->add('plainPassword', RepeatedType::class, [
                                'type' => PasswordType::class,
                                # instead of being set onto the object directly,
                                # this is read and encoded in the controller
                                'mapped' => false,
                                'options' => ['attr' => ['password-field']],
                                'required' => true,
                                'invalid_message' => 'form.password.nomatch',
                                'first_options' => ['label' => 'form.password.password'],
                                'second_options' => ['label' => 'form.password.confirm'],
                                'constraints' => [
                                        new Length([
                                                'min' => 8,
                                                'minMessage' => 'form.password.help',
                                                # max length allowed by Symfony for security reasons
                                                'max' => 4096,
                                        ]),
                                ],
                        ]);
                $builder->add('submit', SubmitType::class, [
                        'label' => 'form.submit'
                ]);
        }
}
