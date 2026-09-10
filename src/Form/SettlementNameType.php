<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettlementNameType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'settlementname_23436',
			'translation_domain' 	=> 'actions',
			'attr'			=> ['class'=>'wide'],
			'name'			=> null,
			'submit'		=> 'control.rename.submit'
		));
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$name = $options['name'];
		$submit = $options['submit'];
		$builder->add('name', TextType::class, array(
			'label'=>'house.create.name',
			'required'=>true,
			'data'=>$name,
			'attr' => array('size'=>30, 'maxlength'=>80)
		));

		$builder->add('submit', SubmitType::class, array('label'=>$submit));
	}
}
