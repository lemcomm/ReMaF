<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class QuestType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'quest_7523',
			'translation_domain' 	=> 'actions',
			'data_class'		=> 'App\Entity\Quest',
			'attr'			=> array('class'=>'wide')
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('summary', TextType::class, array(
			'label'=>'quests.summary',
			'required'=>true,
			'attr' => array('size'=>80, 'maxlength'=>240)
		));
		$builder->add('description', TextareaType::class, array(
			'label'=>'quests.desc',
			'required'=>true,
		));
		$builder->add('reward', TextareaType::class, array(
			'label'=>'quests.reward',
			'required'=>true,
		));

		$builder->add('submit', SubmitType::class, array('label'=>'quests.submit'));
	}
}
