<?php

namespace App\Form;

use App\Entity\Character;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class CharacterSelectType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'character_7141'
		));
		$resolver->setRequired(['characters', 'empty', 'label', 'submit', 'domain', 'required']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$characters = $options['characters'];

		$builder->add('target', EntityType::class, array(
			'placeholder' => $options['empty'],
			'label' => $options['label'],
			'class'=>Character::class,
			'choice_label'=>'name',
			'required'=>$options['required'],
			'query_builder'=>function(EntityRepository $er) use ($characters) {
				$qb = $er->createQueryBuilder('c');
				$qb->where('c IN (:characters)');
				$qb->setParameter('characters', $characters);
				return $qb;
			},
			'translation_domain' => $options['domain'],
		));

		$builder->add('submit', SubmitType::class, array('label'=>$options['submit'], 'translation_domain'=>$options['domain']));
	}


}
