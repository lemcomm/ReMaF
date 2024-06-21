<?php

namespace App\Form;

use App\Entity\Character;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class PrisonersManageType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'prisonersmanage_91356',
			'translation_domain' => 'politics'
		));
		$resolver->setRequired(['prisoners', 'others']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('prisoners', FormType::class);
		$others = $options['others'];

		foreach ($options['prisoners'] as $prisoner) {
			$actions = array(
				'diplomacy.prisoners.free' => 'free',
				'diplomacy.prisoners.execute' => 'execute'
			);
			if (!empty($others) && !$prisoner->hasAction('personal.prisonassign')) {
				$actions['diplomacy.prisoners.assign'] = 'assign';
			}

			$idstring = (string)$prisoner->getId();
			$builder->get('prisoners')->add($idstring, FormType::class, array('label'=>$prisoner->getName()));
			$field = $builder->get('prisoners')->get($idstring);

			$field->add('action', ChoiceType::class, array(
				'choices' => $actions,
				'required' => false,
				'choice_translation_domain' => true,
				'attr' => array('class'=>'action')
			));
			$field->add('method', ChoiceType::class, array(
				'choices' => array(
					'diplomacy.prisoners.kill.behead' => 'behead',
					'diplomacy.prisoners.kill.hang' => 'hang',
					'diplomacy.prisoners.kill.burn' => 'burn',
					'diplomacy.prisoners.kill.quarter' => 'quarter',
					'diplomacy.prisoners.kill.impale' => 'impale'
				),
				'choice_translation_domain' => true,
				'required' => false,
				'placeholder' => 'diplomacy.prisoners.choose',
				'attr' => array('class'=>'method')
			));
		}

		if (!empty($others)) {
			$builder->add('assignto', EntityType::class, array(
				'placeholder' => 'diplomacy.prisoners.choose',
				'label' => 'diplomacy.prisoners.assign',
				'required' => false,
				'class'=>Character::class, 'choice_label'=>'name', 'query_builder'=>function(EntityRepository $er) use ($others) {
					$qb = $er->createQueryBuilder('c');
					$qb->where('c IN (:others)');
					$qb->setParameter('others', $others);
					return $qb;
				},
			));			
		}

	}

}
