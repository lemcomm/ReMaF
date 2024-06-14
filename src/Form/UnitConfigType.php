<?php

namespace App\Form;

use App\Entity\Settlement;
use App\Entity\Unit;
use App\Entity\UnitSettings;
use Doctrine\Common\Collections\ArrayCollection;
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
			'settlements'		=> new ArrayCollection(),
			'data_class'		=> Unit::class,
		));
		$resolver->setRequired(['lord']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$lord = $options['lord'];
		$unit = $builder->getData();
		$settlements = $options['settlements'];

		$renamable = $unit->getRenamable();

		$builder->add('supplier', EntityType::class, array(
			'label' => 'unit.supplier.name',
			'multiple'=>false,
			'expanded'=>false,
			'class'=>Settlement::class,
			'choice_label'=>'name',
			'choices'=>$settlements,
			'placeholder' => 'unit.supplier.empty',
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
				'advance' => 'unit.strategy.advance',
				'hold' => 'unit.strategy.hold',
				'distance' => 'unit.strategy.distance'
			),
			'placeholder'=>'unit.strategy.empty',
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
		));
		$builder->add('respect_fort', CheckboxType::class, array(
			'label'=>'unit.usefort',
			'required'=>false,
		));
		$builder->add('line', ChoiceType::class, array(
			'label'=>'unit.line.name',
			'required'=>false,
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
		$builder->add('submit', SubmitType::class, array('label'=>'button.submit'));
	}
}
