<?php

namespace App\Form;

use App\Entity\Character;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class HouseMembersType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'housemembers_8675309',
			'translation_domain' => 'politics',
			'members'		=> [],
			'notinclude'		=> true,
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$members = $options['members'];
		$notinclude = $options['notinclude'];

		$builder->add('member', EntityType::class, array(
			'label'=>'house.members.member',
			'required' => true,
			'multiple' => false,
			'expanded' => false,
			'class'=>Character::class,
			'choice_label'=>'name',
			'group_by' => function($val, $key, $index) {
				return $val->getHouse()->getName();
			},
			'query_builder'=>function(EntityRepository $er) use ($members) {
				return $er->createQueryBuilder('c')->where('c in (:all)')->setParameter('all', $members)->orderBy('c.name', 'ASC');
			}
		));

		if ($notinclude) {
			$builder->add('submit', SubmitType::class, array('label'=>'house.members.submit'));
		}
	}
}
