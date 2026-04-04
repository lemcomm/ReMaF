<?php

/** @noinspection PhpUnusedPrivateMethodInspection */

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Enum\Activities;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
		$resolver->setRequired(['activity']);
	}
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		/** @var Activity $act */
		$act = $options['activity'];

		$builder->add('which', ChoiceType::class, [
			'choices' => $act->getEventOptions(),
			'expanded' => true,
			'multiple' => true,
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
		$builder->add('submit', SubmitType::class, [
			'label'=>'activity.join.form.submit'
		]);
	}
}
