<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssocDeityWordsType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'wordsdeity_1779',
			'translation_domain' => 'orgs'
		));
		$resolver->setRequired('deity');
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$deity = $options['deity'];
		$builder->add('words', TextareaType::class, array(
			'label'=>'deity.form.new.words',
			'attr' => array('title'=>'deity.help.words'),
			'required'=>false,
			'data'=>$deity->getWords()
		));
		$builder->add('submit', SubmitType::class, array('label'=>'deity.form.submit'));
	}
}
