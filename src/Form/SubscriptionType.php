<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SubscriptionType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'subscription_145615',
			'all_levels'	=> false,
			'current_level'	=> false,
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$choices = array();
		foreach ($options['all_levels'] as $i=>$level) {
			if ($level["selectable"]) {
				$choices[$i] = 'account.level.'.$i;
			}
		}

		$builder->add('level', ChoiceType::class, array(
			'label' => 'account.level.name',
			'required' => true,
			'expanded' => true,
			'choices' => $choices,
			'data'=>$options['current_level']
		));
	}
}
