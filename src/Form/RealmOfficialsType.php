<?php

namespace App\Form;

use App\Entity\Character;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class RealmOfficialsType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'realmofficials_96532',
			'translation_domain' => 'politics',
		));
		$resolver->setRequired(['holders', 'candidates']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$candidates = $options['candidates'];
		$holders = $options['holders'];
		$candidatesIDs = [];
		foreach ($candidates as $candidate) {
			$candidatesIDs[] = $candidate->getId();
		}

		$builder->add('candidates', EntityType::class, array(
			'label'=>'position.appoint.candidates',
			'required' => false,
			'multiple' => true,
			'expanded' => true,
			'data' => $holders,
			'class'=>Character::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($candidatesIDs) {
				return $er->createQueryBuilder('c')->where('c in (:all)')->setParameter('all', $candidatesIDs)->orderBy('c.name', 'ASC');
			}
		));

		$builder->add('submit', SubmitType::class, array('label'=>'position.appoint.submit'));
	}
}
