<?php

namespace App\Form;

use App\Entity\Settlement;
use App\Entity\UnitSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'unitsettings_1337',
			'translation_domain' 	=> 'settings',
			'attr'			=> array('class'=>'wide'),
			'settings'		=> null
		));
		$resolver->setRequired(['lord']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$settings = $options['settings'];
		$lord = $options['lord'];

		$name = null;
		$strategy = null;
		$tactic = null;
		$respect = null;
		$line = null;
		$siege = null;
		$renamable = null;
		$retreat = null;
		$reinforcements = null;

		if ($settings) {
			$name = $settings->getName();
			if ($settings->getStrategy()) {
				$strategy = $settings->getStrategy();
			}
			if ($settings->getTactic()) {
				$tactic = $settings->getTactic();
			}
			if ($settings->getRespectFort()) {
				$respect = $settings->getRespectFort();
			}
			if ($settings->getline()) {
				$line = $settings->getLine();
			}
			if ($settings->getSiegeOrders()) {
				$siege = $settings->getSiegeOrders();
			}
			if ($settings->getRenamable()) {
				$renamable = $settings->getRenamable();
			}
			if ($settings->getRetreatThreshold()) {
				$retreat = $settings->getRetreatThreshold();
			}
			if ($settings->getReinforcements()) {
				$reinforcements = $settings->getReinforcements();
			}
		}
		if($renamable !== false) {
			$builder->add('name', TextType::class, array(
				'label'=>'unit.name',
				'data'=>$name,
				'required'=>true,
			));
		} else {
			$builder->add('name', HiddenType::class, [
				'data'=>$name,
			]);
		}
		$builder->add('strategy', ChoiceType::class, array(
			'label'=>'unit.strategy.name',
			'required'=>false,
			'choices'=>array(
				'advance' => 'unit.strategy.advance',
				'hold' => 'unit.strategy.hold',
				'distance' => 'unit.strategy.distance'
			),
			'placeholder'=>'unit.strategy.empty',
			'data'=>$strategy,
		));
		$builder->add('tactic', ChoiceType::class, array(
			'label'=>'unit.tactic.name',
			'required'=>false,
			'choices'=>array(
				'melee' => 'unit.tactic.melee',
				'ranged' => 'unit.tactic.ranged',
				'mixed' => 'unit.tactic.mixed'
			),
			'placeholder'=>'unit.tactic.empty',
			'data'=>$tactic,
		));
		$builder->add('respect_fort', CheckboxType::class, array(
			'label'=>'unit.usefort',
			'data'=>$respect,
			'required'=>false,
		));
		$builder->add('line', ChoiceType::class, array(
			'label'=>'unit.line.name',
			'required'=>false,
			'data'=>$line,
			'choices'=>array(
				'1' => 'unit.line.1',
				'2' => 'unit.line.2',
				'3' => 'unit.line.3',
				'4' => 'unit.line.4',
				'5' => 'unit.line.5',
				'6' => 'unit.line.6',
				'7' => 'unit.line.7',
			),
			'placeholder'=> 'unit.line.empty',
		));
		$builder->add('siege_orders', ChoiceType::class, array(
			'label'=>'unit.siege_orders.name',
			'required'=>false,
			'data'=>$siege,
			'choices'=>array(
				'assault' => 'unit.siege_orders.assault',
				'hold' => 'unit.siege_orders.hold',
				'equipment' => 'unit.siege_orders.equipment'
			),
			'placeholder'=>'unit.siege_orders.empty',
		));
		if ($lord) {
			$builder->add('renamable', CheckboxType::class, array(
				'label'=>'unit.renamable.name',
				'required'=>false,
				'data'=>$renamable,
			));
		}
		$builder->add('retreat_threshold', NumberType::class, array(
			'label'=>'unit.retreat.name',
			'data'=>$retreat,
			'required'=>false,
		));
		$builder->add('reinforcements', CheckboxType::class, array(
			'label'=>'unit.reinforcements',
			'data'=>$reinforcements,
			'required'=>false,
		));
		$builder->add('submit', SubmitType::class, array('label'=>'button.submit'));
	}
}
