<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;

class NewTokenFormType extends AbstractType {

        public function configureOptions(OptionsResolver $resolver): void {
                $resolver->setDefaults([
                        'translation_domain' => 'core'
                ]);
        }

        public function buildForm(FormBuilderInterface $builder, array $options): void {
                $builder->add('username', TextType::class, [
                        'label' => 'form.username.username',
                                'constraints' => [
                                        new Regex([
                                                'pattern' => '/^[a-zA-Z0-9 \-_]*$/',
                                                'message' => 'form.username.help',
                                        ]),
                                ],
                        ]);
                $builder->add('email', TextType::class, [
                        'label' => 'form.email.email',
                                'constraints' => [
                                        new Email([
                                                'message' => 'form.email.help',
                                        ]),
                                ],
                        ]);
                $builder->add('submit', SubmitType::class, [
                        'label' => 'form.submit'
                ]);
        }
}
