<?php

namespace App\Form;

use App\Entity\Character;
use App\Entity\Place;
use App\Entity\Settlement;
use App\Entity\Siege;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * Form for selecting siege actions and subactions.
 *
 * Accepts the following options (in their legacy order):
 * * 'character' - Character Entity - Character conducting the action
 * * 'location' - Settlement || Place Entity - Location of the Siege
 * * 'siege' - Siege Entity - The siege itself
 * * 'action' - string (null) - Main action being performed
 */
class SiegeType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'csrf_token_id'		=> 'siege_97',
			'translation_domain'	=> 'actions',
			'action'		=> null,
		));
		$resolver->setRequired(['character', 'location', 'siege']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$siege = $options['siege'];
		$location = $options['location'];
		$character = $options['character'];
		$action = $options['action'];
		$isLeader = FALSE;
		$isAttacker = FALSE;
		$isDefender = FALSE;
		$defLeader = FALSE;
		$attLeader = FALSE;
		$actionslist = array();

		if ($siege->getAttacker()->getCharacters()->contains($character)) {
			$isAttacker = TRUE;
		} elseif ($siege->getDefender()->getCharacters()->contains($character)) {
			$isDefender = TRUE;
		}
		if ($siege->getAttacker()->getLeader()) {
			$attLeader = TRUE;
			if ($siege->getAttacker()->getLeader() == $character) {
				$isLeader = TRUE;
			}
		}
		if ($siege->getDefender()->getLeader()) {
			$defLeader = TRUE;
			if ($siege->getDefender()->getLeader() == $character) {
				$isLeader = TRUE;
			}
		}

		#NOTE: $allactions = array('leadership', 'build', 'assault', 'disband', 'leave', 'join', 'assume');
		# Figure out if we're the group leader, and while we're at it, if both groups have leaders.
		if (!$action || $action == 'select') {
			$attCount = $siege->getAttacker()->getCharacters()->count();
			$defCount = $siege->getDefender()->getCharacters()->count();
			/* TODO: Originally the plan was to allow suicide runs, but they make siege battles *messy* with how the groups are handled. For now, no suicide runs.
			if (!$character->isDoingAction('military.regroup')) {
				$actionslist = array('attack' => 'siege.action.attack');
			} else {
				$actionslist = array();
			}
			*/
			# Once we add siege equipment, we'll give everyone the option to build it, regrouping or not.
			# $actionslist = array('build' => 'siege.action.build', 'attack' => 'siege.action.attack');
			if ($isLeader) {
				# Leaders can always transfer leadership, if they're not alone.
				if ($defCount > 1 || $attCount > 1) {
					$actionslist = array_merge($actionslist, array('military.siege.leadership.name' => 'leadership'));
				}
				# Attacker leader can always disband.
				if ($siege->getAttacker()->getLeader() == $character) {
					$actionslist = array_merge($actionslist, array('military.siege.disband.name' => 'disband'));
				}
				if (!$character->isDoingAction('military.regroup')) {
					# Not regrouping? Then you can call an assault if you'r the leader.
					$actionslist = array_merge($actionslist, array('military.siege.assault.name' => 'assault'));
				}
			} else {
				# Anyone that isn't a leader can opt to just leave.
				$actionslist = array_merge($actionslist, array('military.siege.leave.name' => 'leave'));
			}
			if ($isLeader && $siege->getAttacker()->getLeader() != $character) {
				# Defender leader can also just leave, because he might be alone.
				$actionslist = array_merge($actionslist, array('military.siege.leave.name' => 'leave'));
			}
			if ($location instanceof Settlement) {
				if (
					(!$defLeader && $isDefender && $character->getInsideSettlement() == $location && $location->getOwner() == $character)
					|| (!$defLeader && $isDefender && !$location->getCharactersPresent()->contains($location->getOwner()))
					|| (!$attLeader && $isAttacker)
				) {
					# No leader of your group? Defending lord can assume if present, otherwise any defender can. Any attacker can take control of leaderless attackers.
					$actionslist = array_merge($actionslist, array('military.siege.assume.name' => 'assume'));
				}
			} elseif ($location instanceof Place) {
				if (
					(!$defLeader && $isDefender && $character->getInsidePlace() == $location && $location->getOwner() == $character)
					|| (!$defLeader && $isDefender && !$location->getCharactersPresent()->contains($location->getOwner()))
					|| (!$attLeader && $isAttacker)
				) {
					# No leader of your group? Defending lord can assume if present, otherwise any defender can. Any attacker can take control of leaderless attackers.
					$actionslist = array_merge($actionslist, array('military.siege.assume.name' => 'assume'));
				}
			}
			if (!$siege->getBattles()->isEmpty()) {
				# If there's a battle ongoing, anyone can opt to join it. If the leader does, they'll be able to call their entire force into action.
				$actionslist = array_merge($actionslist, array('military.siege.join.name' => 'join'));
			}
			ksort($actionslist, 2); #Sort array as strings.
			$builder->add('action', ChoiceType::class, array(
				'required'=>true,
				'choices' => $actionslist,
				'placeholder'=>'military.siege.no_action',
				'label'=> 'military.siege.actions.all'
			));
		} else {
			$builder->add('action', HiddenType::class, array(
				'data'=>'selected'
			));
			switch($action) {
				case 'leadership':
					$builder->add('subaction', HiddenType::class, array(
						'data'=>'leadership'
					));
					$builder->add('newleader', EntityType::class, array(
						'label'=>'military.siege.actions.leadership.form',
						'required'=>true,
						'placeholder'=>'siege.character.none',
						'attr'=>array('title'=>'siege.help.newleader'),
						'class'=>Character::class,
						'choice_label'=>'name',
						'query_builder'=>function(EntityRepository $er) use ($character, $siege) {
							return $er->createQueryBuilder('c')->leftjoin('c.battlegroups', 'bg')->where(':character = bg.leader')->andWhere('bg.siege = :siege')->andWhere(':character != c')->setParameters(array('character'=>$character, 'siege'=>$siege))->orderBy('c.name', 'ASC');
						}
					));
					break;
				case 'build':
					$builder->add('subaction', HiddenType::class, array(
						'data'=>'build'
					));
					$builder->add('quantity', IntegerType::class, array(
						'attr'=>array('size'=>3)
					));
					/*
					$form->add('type', 'entity', array(
						'label'=>'military.siege.newequpment',
						'required'=>true,
						'placeholder'=>'equipment.none'
						'attr'=>array('title'=>'siege.help.equipmenttype'),
						'class'=>'App:SiegeEquipmentType',
						'choice_label'=>'nameTrans'
						'query_builder'=>function(EntityRepository $er){
							return $er->createQueryBuilder('e')->orderBy('e.name', 'ASC');
						}
					));
					*/
					break;
				case 'assault':
					$builder->add('subaction', HiddenType::class, array(
						'data'=>'assault'
					));
					if ($isDefender) {
						$builder->add('assault', CheckboxType::class, array(
							'label' => 'military.siege.actions.sortie.confirm',
							'required' => true
						));
					} else {
						$builder->add('assault', CheckboxType::class, array(
							'label' => 'military.siege.actions.assault.confirm',
							'required' => true
						));
					}
					break;
				case 'disband':
					$builder->add('subaction', HiddenType::class, array(
						'data'=>'disband'
					));
					$builder->add('disband', CheckboxType::class, array(
						'label' => 'military.siege.actions.disband.confirm',
						'required' => true
					));
					break;
				case 'leave':
					$builder->add('subaction', HiddenType::class, array(
						'data'=>'leave'
					));
					$builder->add('leave', CheckboxType::class, array(
						'label' => 'military.siege.actions.leave.confirm',
						'required' => true
					));
					break;
				/*case 'attack':
					$builder->add('subaction', HiddenType::class, array(
						'data'=>'attack'
					));
					$builder->add('attack', CheckboxType::class, array(
						'label' => 'military.siege.confirm.attack',
						'required' => true
					));
					break;
				case 'joinattack':
					$builder->add('subaction', HiddenType::class, array(
						'data'=>'joinattack'
					));
					$builder->add('join', CheckboxType::class, array(
						'label' => 'siege.join',
						'required' => true
					));
					if ($isLeader) {
						$builder->add('joinall', CheckboxType::class, array(
							'label' => 'siege.joinall',
							'required' => true
						));
					}
					break;*/
				case 'joinsiege':
					$builder->add('subaction', HiddenType::class, array(
						'data'=>'joinsiege'
					));
					# Later we'll extend this to include reinforcing parties, hence the arrays. Those looking to attack the attackers and those looking to attack the defenders but weren't part of the original siege (presumably because they showed up late).
					if ($character->getInsideSettlement() == $location || $character->getInsidePlace() == $location) {
						$sides = array('defenders' => 'military.siege.side.defenders');
					} else {
						$sides = array('attackers' => 'military.siege.side.attackers');
					}
					$builder->add('side', ChoiceType::class, array(
						'required'=>true,
						'choices' => $sides,
						'placeholder'=>'military.siege.side.none',
						'label'=> 'military.siege.actions.join.form'
					));
					break;
				case 'assume':
					$builder->add('subaction', HiddenType::class, array(
						'data'=>'assume'
					));
					$builder->add('assume', CheckboxType::class, array(
						'label' => 'military.siege.actions.assume.confirm',
						'required' => true
					));
					break;
			}
		}
		$builder->add('submit', SubmitType::class, array('label'=>'military.siege.submit'));
	}
}
