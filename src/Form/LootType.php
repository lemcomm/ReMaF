<?php

namespace App\Form;

use App\DataTransformer\SettlementTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for looting a region
 *
 * Accepts the following options (in their legacy order):
 * * 'settlement' - Settlement Entity - Settlement of the region to be looted.
 * * 'em' - EntityManagerInterface
 * * 'inside' - boolean - true if character is inside settlement, false if not.
 */
class LootType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'loot_1541',
			'translation_domain' 	=> 'actions',
			'attr'			=> array('class'=>'wide')
		));
		$resolver->setRequired(['settlement', 'em', 'inside']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		if ($options['inside']) {
			$choices = array(
				'military.settlement.loot.option.thralls' => 'thralls',
				'military.settlement.loot.option.supply' => 'supply',
				'military.settlement.loot.option.resources' => 'resources',
				'military.settlement.loot.option.wealth' => 'wealth',
				'military.settlement.loot.option.burn' => 'burn',
			);
		} else {
			$choices = array(
				'military.settlement.loot.option.thralls' => 'thralls',
				'military.settlement.loot.option.food' => 'supply',
				'military.settlement.loot.option.resources' => 'resources',
				'military.settlement.loot.option.wealth' => 'wealth',
			);
		}
		$builder->add('method', ChoiceType::class, array(
			'label'=>'military.settlement.loot.options',
			'multiple'=>true,
			'expanded'=>true,
			'choice_translation_domain' => true,
			'choices'=>$choices 
		));

		$settlement_transformer = new SettlementTransformer($options['em']);
		$builder->add(
			$builder->create('target', TextType::class, array(
			'label'=>'military.settlement.loot.target',
			'required' => false,
			'attr'=>array('class'=>'settlementselect'),
			))->addModelTransformer($settlement_transformer)
		);

		$builder->add('submit', SubmitType::class, array('label'=>'military.settlement.loot.submit'));
	}
}
