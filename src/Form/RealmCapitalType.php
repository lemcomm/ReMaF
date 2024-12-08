<?php

namespace App\Form;

use App\Entity\Settlement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class RealmCapitalType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'capital_931',
			'translation_domain' => 'politics',
			'attr'					=> array('class'=>'wide')
		));
		$resolver->setRequired(['realm']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$allrealms = $options['realm']->findAllInferiors(true);
		$realms = [];
		foreach ($allrealms as $realm) {
			$realms[] = $realm->getId();
		}
		
		$builder->add('capital', EntityType::class, array(
			'label' => 'realm.capital.estates',
			'multiple'=>false,
			'expanded'=>false,
			'class'=>Settlement::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($realms) {
				$qb = $er->createQueryBuilder('e');
				$qb->where($qb->expr()->in('e.realm', ':realms'))->setParameter('realms', $realms);
				$qb->orderBy('e.name');
				return $qb;
			},
			'mapped'=>false,
		));

		$builder->add('submit', SubmitType::class, array('label'=>'realm.capital.submit'));
	}
}
