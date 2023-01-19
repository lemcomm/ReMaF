<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class GiftType extends AbstractType {

	public function getName() {
		return 'gift';
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention' => 'gift_131',
			'attr'		=> array('class'=>'wide'),
			'invite'	=> false,
			'credits'	=> []
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('email', EmailType::class, array(
			'label'=>'account.gift.email',
			'required'=>true,
			'attr' => array('size'=>40, 'maxlength'=>250)
		));

		$builder->add('credits', ChoiceType::class, array(
			'required'=>true, 
			'label'=>'account.gift.credits',
			'placeholder'=>'form.choose',
			'choices'=>$options['credits']
		));

		$builder->add('message', TextareaType::class, array(
			'label'=>'account.gift.text',
			'trim'=>true,
			'required'=>false
		));

		if ($options['invite']) {
			$submit = 'account.gift.invite';
		} else {
			$submit = 'account.gift.gift';
		}

		$builder->add('submit', SubmitType::class, array(
			'label'=>$submit,
		));

	}


}
