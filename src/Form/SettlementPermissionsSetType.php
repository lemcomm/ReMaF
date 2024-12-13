<?php

namespace App\Form;

use App\Entity\Settlement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;

use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Form for updating settlement owner permissions.
 *
 * Accepts the following options (in their legacy order):
 * * 'settlement' - Settlement Entity - Settlement for which you are editing permissions.
 * * 'me' - Character Entity - Character doing the editing.
 */
class SettlementPermissionsSetType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'sps_41312',
			'translation_domain' => 'actions',
			'data_class' => Settlement::class,
		));
		$resolver->setRequired(['lord', 'me', 's']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$lord = $options['lord'];
		$me = $options['me'];
		/** @var Settlement $settlement */
		$settlement = $options['s'];
		$builder->add('allow_thralls', CheckboxType::class, array(
			'label' => "control.permissions.thralls",
			'required' => false,
			'mapped' => false,
			'data'=>$settlement->getAllowThralls(),
		));
		$builder->add('feed_soldiers', CheckboxType::class, array(
			'label' => "control.permissions.feedsoldiers",
			'required' => false,
			'mapped' => false,
			'data'=>$settlement->getFeedSoldiers(),
		));
		$builder->add('food_provision_limit', PercentType::class, [
			'label'=>'control.permissions.foodlimit',
			'required' => false,
			'mapped' => false,
			'data' => $settlement->getFoodProvisionLimit(),
			'constraints'=>[
				new Positive(['message'=>'number.positive']),
				new LessThanOrEqual([
					'value'=>2,
					'message'=>'percent.lessthan200'
				])
			]
		]);
		if ($lord) {
			$builder->add('permissions', CollectionType::class, array(
				'entry_type'		=> SettlementPermissionsType::class,
				'entry_options'	=> [
					'me'=>$me,
					'settlement'=>$settlement,
				],
				'allow_add'	=> true,
				'allow_delete' => true,
				'constraints' => new Valid(),
				'mapped' => true,
			));
		} else {
			$builder->add('occupation_permissions', CollectionType::class, array(
				'entry_type'		=> SettlementOccupationPermissionsType::class,
				'entry_options'	=> [
					'me'=>$me,
					'settlement'=>$settlement,
				],
				'allow_add'	=> true,
				'allow_delete' => true,
				'constraints' => new Valid(),
				'mapped' => true,
			));
		}
	}
}
