<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RealmCreationType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'newrealm_1845',
			'translation_domain' => 'politics'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('name', TextType::class, array(
			'label'=>'realm.name',
			'required'=>true,
			'attr' => array(
				'size'=>20,
				'maxlength'=>40
			)
		));
		$builder->add('formal_name', TextType::class, array(
			'label'=>'realm.formalname',
			'required'=>true,
			'attr' => array(
				'size'=>40,
				'maxlength'=>160
			)
		));

		$realmtypes = array();
		for ($i=1;$i<9;$i++) {
			$realmtypes['realm.type.'.$i] = $i;
		}

		$builder->add('type', ChoiceType::class, array(
			'required'=>true, 
			'choices' => $realmtypes,
			'label'=> 'realm.designation',
			'placeholder' => 'realm.new.choose',
		));

		$builder->add('submit', SubmitType::class, [
			'label'=>'realm.manage.submit'
		]);
	}
}
