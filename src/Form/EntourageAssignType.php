<?php

namespace App\Form;

use App\Entity\Entourage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class EntourageAssignType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'assign_123956',
			'translation_domain' => 'actions'
		));
		$resolver->setRequired(['actions', 'entourage']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$entourage = $options['entourage'];

		if (is_array($options['actions'])) {
			$builder->add('action', ChoiceType::class, array(
				'label' => 'entourage.assign.action',
				'placeholder' => 'form.choose',
				'choice_translation_domain' => true,
				'choices' => $options['actions']
			));
		} else {
			$builder->add('action', HiddenType::class, array(
				'data' => $options['actions']
			));
		}

		$builder->add('entourage', EntityType::class, array(
			'label' => 'entourage.assign.select',
			'placeholder' => 'form.choose',
			'multiple'=>true,
			'class'=>Entourage::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($entourage) {
				$qb = $er->createQueryBuilder('e');
				$qb->where('e IN (:entourage)');
				$qb->setParameter('entourage', $entourage->toArray());
				return $qb;
			},
		));
	}

}
