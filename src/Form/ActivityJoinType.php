<?php

/** @noinspection PhpUnusedPrivateMethodInspection */

namespace App\Form;

use App\Entity\Activity;
use App\Entity\EquipmentType;
use App\Enum\Activities;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityJoinType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'activitySelect_12331',
			'translation_domain' 	=> 'activity',
			'maxdistance' => null,
			'me' => null,
		));
		$resolver->setRequired(['activity', 'weapons', 'armor']);
	}
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		/** @var Activity $act */
		$act = $options['activity'];
		$armor = $options['armor'];
		$weapons = $options['weapons'];

		$builder->add('which', ChoiceType::class, [
			'choices' => $act->getEventOptions(),
			'expanded' => true,
			'multiple' => true,
			'required' => true,
			'label' => 'activity.join.form.which',
			'choice_label' => function ($choice) {
				if ($choice === Activities::fightsSolo->value) {
					return 'tourn.form.fightTypes.solo';
				} elseif ($choice === Activities::fightsDuo->value) {
					return 'tourn.form.fightTypes.duo';
				} elseif ($choice === Activities::fightsTeam->value) {
					return 'tourn.form.fightTypes.team';
				} elseif ($choice === Activities::fightsFFA->value) {
					return 'tourn.form.fightTypes.ffa';
				} elseif ($choice === 'joust') {
					return 'tourn.form.joustTypes.joust';
				} elseif ($choice === 'race') {
					return 'tourn.form.racesTypes.races';
				} elseif ($choice === 'fishing') {
					return 'fishing.join';
				} else {
					return 'hunt.join';
				}
			}
		]);
		if (is_array($weapons) && count($weapons) > 1) {
			$builder->add('weapon', EntityType::class, array(
				'label'=>'meta.loadout.weapon',
				'placeholder'=>'meta.loadout.none',
				'translation_domain' => 'actions',
				'required'=>true,
				'choice_label'=>'nameTrans',
				'choice_translation_domain' => 'messages',
				'class'=>EquipmentType::class,
				'choices'=>$weapons
			));
		} else {
			$builder->add('weapon', HiddenType::class, [
				'data'=>null
			]);
		}
		if ($armor) {
			$builder->add('armor', EntityType::class, array(
				'label'=>'meta.loadout.armor',
				'placeholder'=>'meta.loadout.none',
				'translation_domain' => 'actions',
				'required'=>true,
				'choice_label'=>'nameTrans',
				'choice_translation_domain' => 'messages',
				'class'=>EquipmentType::class,
				'choices'=>$options['armor']
			));
		} else {
			$builder->add('armor', HiddenType::class, [
				'data'=>null,
			]);
		}
		$builder->add('submit', SubmitType::class, [
			'label'=>'activity.join.form.submit'
		]);
	}
}
