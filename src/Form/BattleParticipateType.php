<?php

namespace App\Form;

use App\Entity\BattleGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * Form for selecting a battle to join.
 *
 * Accepts the following options (in their legacy order):
 * * 'battles' - array - battles that can be joined.
 */
class BattleParticipateType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'battleparticipate_5106',
		));
		$resolver->setRequired(['battles']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$battles = $options['battles'];

		$builder->add('group', EntityType::class, array(
			'label'=>'battlegroup',
			'placeholder'=>'form.choose',
			'class'=>BattleGroup::class,
			'choice_label'=>'id',
			'query_builder'=>function(EntityRepository $er) use ($battles) {
				$qb = $er->createQueryBuilder('g')->innerJoin('g.battle', 'b');
				$qb->where('b IN (:battles)');
				$qb->setParameter('battles', $battles);
				return $qb;
		}));
	}
}
