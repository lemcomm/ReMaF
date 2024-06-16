<?php

namespace App\Form;

use App\Entity\Settlement;
use App\Entity\Unit;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Positive;

/**
 * Form for configuring unit settings.
 *
 * Accepts the following options (in their legacy order):
 * * 'supply' - boolean - Determines if supply cane be changed.
 * * 'settlements' - array - Settlements that can be used for supply
 * * 'settings' - UnitSettings Entity (null) - UnitSettings entity to edit
 * * 'lord' - boolean - Determines if lord only options appear
 */
class UnitBulkType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'bulkunitupdate_432432',
			'translation_domain' 	=> 'settings',
			'attr'			=> array('class'=>'wide'),
			'settlements'		=> new ArrayCollection()
		));
		$resolver->setRequired(['units']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$units = $options['units'];
		$settlements = $options['settlements'];
		$builder->add('units', EntityType::class, [
			'label'=>'unit.bulk.select',
			'multiple'=>true,
			'class'=>Unit::class,
			'choice_label'=>'name',
			'expanded'=>true,
			'choices'=>$units,
			'mapped'=>false,
			'choice_value'=>'id'
		]);
		$builder->add('supplier', EntityType::class, [
			'label'=>'unit.supplier.name',
			'choices'=>$settlements,
			'placeholder'=>'unit.nochange',
			'class'=>Settlement::class,
			'choice_label'=>function (Settlement $s): string {
				return $s->getName().' ('.$s->getId().')';
			},
			'required'=>false,
			'mapped'=>false,
			'choice_value'=>'id'
		]);
		$builder->add('strategy', ChoiceType::class, array(
			'label'=>'unit.strategy.name',
			'required'=>false,
			'choices'=>array(
				'unit.strategy.advance' => 'advance',
				'unit.strategy.hold' => 'hold',
				'unit.strategy.distance' => 'distance'
			),
			'placeholder'=>'unit.nochange',
		));
		$builder->add('tactic', ChoiceType::class, array(
			'label'=>'unit.tactic.name',
			'required'=>false,
			'choices'=>array(
				'unit.tactic.melee' => 'melee',
				'unit.tactic.ranged' => 'ranged',
				'unit.tactic.mixed' => 'mixed'
			),
			'placeholder'=>'unit.nochange',
		));
		$builder->add('respect_fort', CheckboxType::class, array(
			'label'=>'unit.usefort',
			'required'=>false,
		));
		$builder->add('line', ChoiceType::class, array(
			'label'=>'unit.line.name',
			'required'=>false,
			'choices'=>array(
				'unit.line.1' => '1',
				'unit.line.2' => '2',
				'unit.line.3' => '3',
				'unit.line.4' => '4',
				'unit.line.5' => '5',
				'unit.line.6' => '6',
				'unit.line.7' => '7',
			),
			'placeholder'=> 'unit.nochange',
		));
		$builder->add('siege_orders', ChoiceType::class, array(
			'label'=>'unit.siege_orders.name',
			'required'=>false,
			'choices'=>array(
				'unit.siege_orders.assault' => 'assault',
				'unit.siege_orders.hold' => 'hold',
				'unit.siege_orders.equipment' => 'equipment'
			),
			'placeholder'=>'unit.nochange',
		));
		$builder->add('retreat_threshold', NumberType::class, array(
			'label'=>'unit.retreat.name',
			'required'=>false,
		));
		$builder->add('reinforcements', CheckboxType::class, array(
			'label'=>'unit.reinforcements',
			'required'=>false,
		));
		$builder->add('consumption', PercentType::class, [
			'label'=>'unit.consumption',
			'constraints'=>[
				new Positive(['message'=>'number.positive']),
				new LessThanOrEqual([
					'value'=>1.2,
					'message'=>'perecent.lessthan120'
				])
			],
			'required'=>false,
		]);
		$builder->add('provision', PercentType::class, [
			'label'=>'unit.provisioning',
			'constraints'=>[
				new Positive(['message'=>'number.positive']),
				new LessThanOrEqual([
					'value'=>2,
					'message'=>'percent.lessthan200'
				])
			],
			'required'=>false,
		]);
		$builder->add('submit', SubmitType::class, array('label'=>'button.submit'));
	}
}
