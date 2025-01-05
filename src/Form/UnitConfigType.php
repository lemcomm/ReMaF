<?php

namespace App\Form;

use App\Entity\Settlement;
use App\Entity\Unit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
class UnitConfigType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'unitsettings_1337',
			'translation_domain' 	=> 'settings',
			'attr'			=> array('class'=>'wide'),
			'settlements'		=> new ArrayCollection(),
			'data_class'		=> Unit::class,
			'here'			=> null,
		));
		$resolver->setRequired(['lord']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$lord = $options['lord'];
		$unit = $builder->getData();
		$settlements = $options['settlements'];
		$preferred = [$options['here']];

		$renamable = $unit->getRenamable();

		$builder->add('supplier', EntityType::class, array(
			'label' => 'unit.supplier.name',
			'multiple'=>false,
			'expanded'=>false,
			'class'=>Settlement::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($settlements) {
				$qb = $er->createQueryBuilder('s');
				$qb->where('s IN (:settlements)')
				->setParameters(array('settlements'=>$settlements));
				$qb->orderBy('s.name');
				return $qb;
			},
			'placeholder' => 'unit.supplier.empty',
			'preferred_choices' => $preferred,
			'mapped'=>true,
		));

		if($lord || $renamable !== false) {
			$builder->add('name', TextType::class, array(
				'label'=>'unit.name',
				'required'=>true,
			));
		} else {
			$builder->add('name', HiddenType::class);
		}
		$builder->add('strategy', ChoiceType::class, array(
			'label'=>'unit.strategy.name',
			'required'=>false,
			'choices'=>array(
				'unit.strategy.advance' => 'advance',
				'unit.strategy.hold' => 'hold',
				'unit.strategy.distance' => 'distance'
			),
			'placeholder'=>'unit.strategy.empty',
		));
		$builder->add('tactic', ChoiceType::class, array(
			'label'=>'unit.tactic.name',
			'required'=>false,
			'choices'=>array(
				'unit.tactic.melee' => 'melee',
				'unit.tactic.ranged' => 'ranged',
				'unit.tactic.mixed' => 'mixed'
			),
			'placeholder'=>'unit.tactic.empty',
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
			'placeholder'=> 'unit.line.empty',
		));
		$builder->add('siege_orders', ChoiceType::class, array(
			'label'=>'unit.siege_orders.name',
			'required'=>false,
			'choices'=>array(
				'unit.siege_orders.assault' => 'assault',
				'unit.siege_orders.hold' => 'hold',
				'unit.siege_orders.equipment' => 'equipment'
			),
			'placeholder'=>'unit.siege_orders.empty',
		));
		if ($lord) {
			$builder->add('renamable', CheckboxType::class, array(
				'label'=>'unit.renamable.name',
				'required'=>false,
			));
		} else {
			$builder->add('renamable', HiddenType::class);
		}
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
			]
		]);
		$builder->add('provision', PercentType::class, [
			'label'=>'unit.provisioning',
			'constraints'=>[
				new Positive(['message'=>'number.positive']),
				new LessThanOrEqual([
					'value'=>2,
					'message'=>'percent.lessthan200'
				])
			]
		]);
		$builder->add('submit', SubmitType::class, array('label'=>'button.submit'));
	}
}
