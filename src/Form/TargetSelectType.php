<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TargetSelectType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'targetselect_513',
			'translation_domain' => 'dungeons',
			'attr'					=> array('class'=>'targetselect')
		));
		$resolver->setRequired(['type', 'choices', 'current']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$type = $options['type'];
		$choices = $options['choices'];
		$current = $options['current'];
		$builder->add('type', HiddenType::class, array('data'=>$type));
		$options = array(
			'label' => false,
			'required' => true,
			'choices' => $choices
		);
		if ($current) {
			$options['data'] = $current;
		}
		$builder->add('target', ChoiceType::class, $options);
	}

}
