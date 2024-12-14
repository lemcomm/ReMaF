<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class CharacterSettingsType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'charactersettings_671',
			'translation_domain' => 'settings',
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('withdrawLevel', PercentType::class, [
			'label'=>'character.withdrawalLevel',
			'required'=>false,
			'constraints'=> [
				new GreaterThanOrEqual([
					'value' => 0.25,
				]),
				new LessThanOrEqual([
					'value' => 1
				]),
			],
		]);
		$builder->add('auto_read_realms', CheckboxType::class, array(
			'label'=>'character.auto.readrealms',
			'required'=>false,
		));
		$builder->add('auto_read_assocs', CheckboxType::class, array(
			'label'=>'character.auto.readassocs',
			'required'=>false,
		));
		$builder->add('auto_read_house', CheckboxType::class, array(
			'label'=>'character.auto.readhouse',
			'required'=>false,
		));
		$builder->add('non_hetero_options', CheckboxType::class, array(
			'label'=>'character.non_hetero_options',
			'required'=>false,
		));
		$builder->add('submit', SubmitType::class, array('label'=>'submit'));
	}
}
