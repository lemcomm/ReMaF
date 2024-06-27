<?php

namespace App\Form;

use App\Entity\EquipmentType;
use App\Entity\Unit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Form for managing soldiers in a unit.
 *
 * Accepts the following options:
 * * 'soldiers' - array/ArrayCollection - Array of soldiers in the unit.
 * * 'available_resupply' - array - Items that can be resupplied.
 * * 'available_training' - array - Items that can be trained.
 * * 'others' - array - Other units soldiers can be reassigned to.
 * * 'reassign' - boolean - True if soldiers can be reassigned.
 * * 'unit' - Unit Entity - The unit in question.
 * * 'me' - Character Entity - The character conducting the management of the unit.
 * * 'hasUnitPerm' - boolean - Does this character has management permissions for this unit.
 */
class UnitSoldiersType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'soldiersmanage_1533',
			'translation_domain' => 'actions'
		));
		$resolver->setRequired(['soldiers', 'available_resupply', 'available_training', 'others', 'reassign', 'unit', 'me', 'hasUnitPerm']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		if (is_array($options['soldiers'])) {
			$soldiers = $options['soldiers'];
		} else {
			$soldiers = $options['soldiers']->toArray();
		}

		$avail_train = array();
		foreach ($options['available_training'] as $a) {
			$avail_train[] = $a['item']->getId();
		}
		$available_resupply = new ArrayCollection();
		foreach ($options['available_resupply'] as $r) {
			$available_resupply->add($r['item']);
		}

		$builder->add('npcs', FormType::class);
		$in_battle = -1;
		$is_looting = -1;
		$unit = $options['unit'];
		$me = $options['me'];
		$others = $options['others'];
		$reassign = $options['reassign'];
		$hasUnitPerm = $options['hasUnitPerm'];


		$local = false;
		$locked = false;
		/** @var Unit $unit */

		if ($unit->getTravelDays() > 0) {
			$locked = true;
		} elseif ($unit->getCharacter() == $me || (!$unit->getCharacter() && ($hasUnitPerm || $unit->getMarshal() == $me))) {
			$local = true;
		}

		foreach ($soldiers as $soldier) {
			$actions = [];
			if ($in_battle == -1) {
				if ($soldier->getCharacter()) {
					$in_battle = $soldier->getCharacter()->isInBattle();
					$is_looting = $soldier->getCharacter()->isLooting();
				} else {
					$in_battle = false;
					$is_looting = false;
				}
			}
			$idstring = (string)$soldier->getId();
			$builder->get('npcs')->add($idstring, FormType::class, array('label'=>$soldier->getName()));
			$field = $builder->get('npcs')->get($idstring);

			if ($soldier->isLocked() || $is_looting) {
				// disallow almost all actions if soldier is locked or if character is in a battle or looting
				if (!$soldier->isAlive() && $local) {
					$actions = array('recruit.manage.bury2' => 'bury');
				}
			} else {
				if ($soldier->isAlive()) {
					if (!$in_battle) {
						if ($local) {
							if (!empty($avail_train) && $soldier->isActive()) {
								$actions['recruit.manage.retrain'] = 'recruit';
								$actions['recruit.manage.disband'] = 'disband';
							}
							if ($reassign) {
								$actions['recruit.manage.reassign'] = 'assignto';
								$actions['recruit.manage.disband'] = 'disband';
								# This might be duplicate, or it might not. Array key, so doesn't matter.
							}
						}
					}
					$resupply = false;
					if (!$available_resupply->isEmpty()) {
						if ( (!$soldier->getHasWeapon() && $available_resupply->contains($soldier->getTrainedWeapon()))
							|| (!$soldier->getHasArmour() && $available_resupply->contains($soldier->getTrainedArmour()))
							|| (!$soldier->getHasEquipment() && $available_resupply->contains($soldier->getTrainedEquipment()))
							|| (!$soldier->getHasMount() && $available_resupply->contains($soldier->getTrainedMount()))
						) {
							$resupply = true;
						}
					}
					if ($resupply) {
						$actions['recruit.manage.resupply'] = 'resupply';
					}
				} else {
					$actions = array('recruit.manage.bury' => 'bury');
				}
			} // endif locked
			if (!empty($actions)) {
				$field->add('action', ChoiceType::class, array(
					'choices' => $actions,
					'required' => false,
					'attr' => array('class'=>'action'),
					'disabled' => $locked,
				));
			}
		}
		if (!empty($others) && $reassign) {
			$builder->add('assignto', EntityType::class, array(
				'placeholder' => 'form.choose',
				'label' => 'recruit.manage.assignto',
				'required' => false,
				'choice_label'=>'name',
				'class'=>Unit::class,
				'query_builder'=>function(EntityRepository $er) use ($others) {
					$qb = $er->createQueryBuilder('u');
					$qb->where('u IN (:others)');
					$qb->setParameter('others', $others);
					return $qb;
				},
				'disabled' => $locked,
			));
		}
		if (!empty($avail_train)) {
			$fields = array('weapon', 'armour', 'equipment', 'mount');
			foreach ($fields as $field) {
				$builder->add($field, EntityType::class, array(
					'label'=>$field,
					'placeholder'=>'item.current',
					'required'=>false,
					'translation_domain'=>'messages',
					'class'=> EquipmentType::class,
					'choice_label'=>'nameTrans',
					'choice_translation_domain'=>'messages',
					'query_builder'=>function(EntityRepository $er) use ($avail_train, $field) {
						return $er->createQueryBuilder('e')->where('e in (:available)')->andWhere('e.type = :type')->orderBy('e.name')
							->setParameters(array('available'=>$avail_train, 'type'=>$field));
					},
					'disabled' => $locked,
				));
			}
		}
	}

}
