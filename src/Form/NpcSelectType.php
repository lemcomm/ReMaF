<?php

namespace App\Form;

use App\Entity\Character;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class NpcSelectType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'	=> 'npc_select_5214',
			'freeNPCs'	=> []
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$characters = $options['freeNPCs'];

		$builder->add('npc', EntityType::class, array(
			'placeholder' => 'form.choose',
			'label' => 'bandits.choose',
			'required' => true,
			'class'=>Character::class, 'choice_label'=>'name', 'query_builder'=>function(EntityRepository $er) use ($characters) {
				$qb = $er->createQueryBuilder('c');
				$qb->where('c IN (:characters)');
				$qb->setParameter('characters', $characters);
				return $qb;
			},
		));

		$builder->add('submit', SubmitType::class, array('label'=>'bandits.submit'));
	}


}
