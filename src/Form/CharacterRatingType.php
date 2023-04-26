<?php

namespace App\Form;

use App\Entity\CharacterRating;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterRatingType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'characterrating_96515',
			'data_class'	=> CharacterRating::class,
			'attr'		=> array('class'=>'wide')
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('content', TextareaType::class, array(
			'label'=>'rating.content',
			'required'=>true,
			'trim'=>true,
			'attr' => array('rows'=>3, 'maxChars'=>200)
		));
		$builder->add('respect', ChoiceType::class, array(
			'label'=>'rating.respect.label',
			'required'=>true,
			'choices'=>array('0'=>'rating.none', '1'=>'rating.yes', '-1'=>'rating.no'),
			'attr' => array('title'=>'rating.respect.help'),
		));
		$builder->add('honor', ChoiceType::class, array(
			'label'=>'rating.honor.label',
			'required'=>true,
			'choices'=>array('0'=>'rating.none', '1'=>'rating.yes', '-1'=>'rating.no'),
			'attr' => array('title'=>'rating.honor.help'),
		));
		$builder->add('trust', ChoiceType::class, array(
			'label'=>'rating.trust.label',
			'required'=>true,
			'choices'=>array('0'=>'rating.none', '1'=>'rating.yes', '-1'=>'rating.no'),
			'attr' => array('title'=>'rating.trust.help'),
		));

		$builder->add('submit', SubmitType::class, array('label'=>'rating.submit'));
	}
}
