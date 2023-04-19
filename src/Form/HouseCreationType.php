<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HouseCreationType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'housecreation_78315',
			'translation_domain' 	=> 'politics',
			'name'			=> null,
			'motto'			=> null,
			'desc'			=> null,
			'priv'			=> null,
			'secret'		=> null,
		));
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$name = $options['name'];
		$motto = $options['motto'];
		$desc = $options['desc'];
		$priv = $options['priv'];
		$secret = $options['secret'];
		$builder->add('name', TextType::class, array(
			'label'=>'house.create.name',
			'required'=>true,
			'data'=>$name,
			'attr' => array('size'=>30, 'maxlength'=>80)
		));
		$builder->add('motto', TextType::class, array(
			'label'=>'house.create.motto',
			'required'=>false,
			'data'=>$motto,
			'attr' => array('size'=>30, 'maxlength'=>200)
		));
		$builder->add('description', TextareaType::class, array(
			'label'=>'house.create.description',
			'trim'=>true,
			'required'=>false,
			'data'=>$desc
		));
		$builder->add('private', TextareaType::class, array(
			'label'=>'house.create.private',
			'trim'=>true,
			'required'=>false,
			'data'=>$priv
		));
		$builder->add('secret', TextareaType::class, array(
			'label'=>'house.create.secret',
			'trim'=>true,
			'required'=>false,
			'data'=>$secret
		));

		$builder->add('submit', SubmitType::class, array('label'=>'house.create.submit'));
	}
}
