<?php

/** @noinspection PhpUnusedPrivateMethodInspection */

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Enum\Activities;

class ActivitySelectType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'activitySelect_12331',
			'translation_domain' 	=> 'activity',
			'maxdistance' => null,
			'me' => null,
		));
		$resolver->setRequired(['activityType', 'subselect']);
	}
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$action = $options['activityType'];

		$method = $action."Fields";
		if (method_exists(__CLASS__, $method)) {
			$this->$method($builder, $options);
		}

		$builder->add('submit', SubmitType::class, [
			'label'=>$action.'.form.submit'
		]);
	}

	private function fishingFields(FormBuilderInterface $builder, array $options): void {
		$whereCan = $options['subselect'];
		$choices = [];
		foreach ($whereCan as $key => $value) {
			if ($value) {
				switch ($key) {
					case 'inland':
						$choices['fishing.form.inland'] = $key;
						break;
					case 'deepwater':
						$choices['fishing.form.deepwater'] = $key;
						break;
					case 'river':
						$choices['fishing.form.river'] = $key;
						break;
					case 'lake':
						$choices['fishing.form.lake'] = $key;
						break;
					case 'coast':
						$choices['fishing.form.coast'] = $key;
						break;
				}
			}
		}
		$builder->add('where', ChoiceType::class, [
			'label' => 'fishing.form.where',
			'choices' => $choices,
			'required' => true,
		]);
	}

	private function tournFields(FormBuilderInterface $builder, array $options): void {
		$types = $options['subselect']['types'];
		$builder->add('name', TextType::class, array(
			'label'=>'tourn.form.name',
			'required'=>true,
			'attr' => ['size'=>50]
		));
		$builder->add('delay', ChoiceType::class, array(
			'label'=>'tourn.form.delay.label',
			'required'=>true,
			'choices'=>array(
				24,
				48,
				72,
				96
			),
			'choice_label' => function ($choice) {
				if ($choice === 24) {
					return 'tourn.form.delay.24';
				} elseif ($choice === 48) {
					return 'tourn.form.delay.48';
				} elseif ($choice === 72) {
					return 'tourn.form.delay.72';
				} else {
					return 'tourn.form.delay.96';
				}
			}

		));
		if ($types['fights']) {
			$tr = 'tourn.form.fightTypes.';
			$choices = [
				'solo' => Activities::fightsSolo->value,
				'duo' => Activities::fightsDuo->value,
				'team' => Activities::fightsTeam->value,
				'ffa' => Activities::fightsFFA->value,
			];
			if ($types['grand']) {
				$choices[] = ['all' => Activities::fightsAll->value];
			}
			$builder->add('fightTypes', ChoiceType::class, [
				'label'=>$tr.'label',
				'multiple'=>$types['grand'],
				'required'=>true,
				'choices'=> $choices,
				'expanded'=>true,
				'choice_label' => function ($choice) {
					if ($choice === Activities::fightsDuo->value) {
						return 'tourn.form.fightTypes.duo';
					}
					if ($choice === Activities::fightsTeam->value) {
						return 'tourn.form.fightTypes.team';
					}
					if ($choice === Activities::fightsFFA->value) {
						return 'tourn.form.fightTypes.ffa';
					}
					if ($choice === Activities::fightsAll->value) {
						return 'tourn.form.fightTypes.all';
					}
					return 'tourn.form.fightTypes.solo';
				},
			]);
			$builder->add('weapon', EntityType::class, [
				'class'=>EquipmentType::class,
				'choice_label'=>'nameTrans',
				'choice_translation_domain' => 'messages',
				'choices'=> $options['subselect']['weapons'],
				'label'=>$tr.'weapon',
				'multiple'=>true,
				'expanded'=>true,
				'required'=>false,
			]);
			$builder->add('armor', CheckboxType::class, [
				'required' => false,
				'label'=>$tr.'armor',
			]);
		} else {
			$builder->add('fightTypes', HiddenType::class, [
				'data'=>false,
			]);
		}
		if ($types['jousts']) {
			$tr = 'tourn.form.joustTypes.';
			$builder->add('joustTypes', CheckboxType::class, [
				'label'=>$tr.'label',
				'required' => false,
			]);
		} else {
			$builder->add('joustTypes', HiddenType::class, [
				'data'=>false,
			]);
		}
		if ($types['races']) {
			$tr = 'tourn.form.racesTypes.';
			$builder->add('racesTypes', CheckboxType::class, [
				'label'=>$tr.'label',
				'required' => false,
			]);
		} else {
			$builder->add('racesTypes', HiddenType::class, [
				'data'=>false,
			]);
		}
	}

	private function duelFields(FormBuilderInterface $builder, array $options): void {
		$me = $options['me'];
		$maxdistance = $options['maxdistance'];
		$subselect = $options['subselect'];
		$builder->add('name', TextType::class, array(
			'label'=>'duel.form.name',
			'required'=>false
		));

		$builder->add('target', EntityType::class,[
			'label'=>'duel.form.challenger',
			'placeholder'=>null,
			'multiple'=>false,
			'expanded'=>false,
			'required'=>true,
			'class'=>Character::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($me, $maxdistance) {
				$qb = $er->createQueryBuilder('c');
				$qb->from(Character::class, 'me');
				$qb->where('c.alive = true');
				$qb->andWhere('c.prisoner_of IS NULL');
				$qb->andWhere('c.system NOT LIKE :gm OR c.system IS NULL')->setParameter('gm', 'GM');
				$qb->andWhere('me = :me')->andWhere('c != me')->setParameter('me', $me);
				if ($maxdistance) {
					$qb->andWhere('ST_Distance(me.location, c.location) < :maxdistance')->setParameter('maxdistance', $maxdistance);
				}
				if (!$me->getInsideSettlement()) {
					// if I am not inside a settlement, I can only attack others who are outside as well
					$qb->andWhere('c.inside_settlement IS NULL');
				}
				$qb->orderBy('c.name', 'ASC');
				return $qb;
		}]);
		$builder->add('context', ChoiceType::class, options: array(
			'label'=>'duel.form.context',
			'required'=>true,
			'choices'=>array(
				'duel.form.first blood' => 'first blood',
				'duel.form.wound' => 'wound',
				'duel.form.surrender' => 'surrender',
				'duel.form.death' => 'death',
			),
			'placeholder'=> 'duel.form.choose'
		));
		$builder->add('sameWeapon', CheckboxType::class, array(
			'label'=>'duel.form.sameWeapon',
			'required'=>false
		));
		$builder->add('weapon', EntityType::class, [
			'class'=>EquipmentType::class,
                        'choice_label'=>'nameTrans',
                        'choice_translation_domain' => 'messages',
                        'choices'=>$subselect,
                        'label'=>'loadout.weapon',
                        'placeholder'=>'loadout.none',
			'translation_domain'=>'settings'
		]);
		$builder->add('weaponOnly', CheckboxType::class, array(
			'label'=>'duel.form.weaponOnly',
			'required'=>false
		));
	}

}
