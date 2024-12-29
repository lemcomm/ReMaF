<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HouseSubcreateType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'	=> 'housesubcreate_4321',
			'translation_domain' => 'politics'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('subject', TextType::class, array(
			'label' => 'house.subcreate.subject',
			'required' => true
		));
		$builder->add('text', TextareaType::class, array(
			'label' => 'house.subcreate.text',
			'required' => true
		));

		$builder->add('submit', SubmitType::class, array('label'=>'request.generic.submit', 'translation_domain' => 'actions'));
	}
}
