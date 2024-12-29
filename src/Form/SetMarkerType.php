<?php

namespace App\Form;

use App\Entity\Realm;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class SetMarkerType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'	=> 'setmarker_19283',
			'realms'	=> new ArrayCollection(),
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('name', TextType::class, array(
			'label'=>'marker.name',
			'required'=>true,
			'empty_data'=>'(unnamed)',
			'attr' => array('size'=>20, 'maxlength'=>60)
		));

		$builder->add('type', ChoiceType::class, array(
			'label'=>'marker.type',
			'required'=>true,
			'choices'=>array('waypoint'=>'marker.waypoint', 'enemy'=>'marker.enemy')
		));

		$realms = array();
		foreach ($options['realms'] as $realm) {
			$realms[] = $realm->getId();
		}
		$builder->add('realm', EntityType::class, array(
			'label'=>'marker.realm',
			'required'=>true,
			'class'=>Realm::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($realms) {
				$qb = $er->createQueryBuilder('r');
				$qb->where('r IN (:realms)');
				$qb->setParameter('realms', $realms);
				return $qb;
			},
		));

		// this is "new" because of the JS dependency which is "new" because of the form field in feature construction
		$builder->add('new_location_x', HiddenType::class, array('required'=>false));
		$builder->add('new_location_y', HiddenType::class, array('required'=>false));

		$builder->add('submit', SubmitType::class, array('label'=>'marker.submit'));
	}

	public function getBlockPrefix(): string {
		return "setmarker";
	}
}
