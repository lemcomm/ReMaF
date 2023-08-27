<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssocJoinType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'assocjoin_8',
			'translation_domain' => 'orgs'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('subject', TextType::class, array(
			'label' => 'assoc.form.join.subject',
			'required' => true
		));
		$builder->add('text', TextareaType::class, array(
			'label' => 'assoc.form.join.text',
			'required' => true
		));
		$builder->add('sure', CheckboxType::class, array(
			'label' => 'assoc.form.areyousure',
			'required' => true
		));

		$builder->add('submit', SubmitType::class, array('label'=>'assoc.form.submit'));
	}
}
