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
				'free' => 'diplomacy.prisoners.free',
				'execute' => 'diplomacy.prisoners.execute'
			);
			if (!empty($others) && !$prisoner->hasAction('personal.prisonassign')) {
				$actions['assign'] = 'diplomacy.prisoners.assign';
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
					'behead'	=> 'diplomacy.prisoners.kill.behead',
					'hang' => 'diplomacy.prisoners.kill.hang',
					'burn' => 'diplomacy.prisoners.kill.burn',
					'quarter' => 'diplomacy.prisoners.kill.quarter',
					'impale' => 'diplomacy.prisoners.kill.impale'
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
