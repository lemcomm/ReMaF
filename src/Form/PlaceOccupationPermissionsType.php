<?php

namespace App\Form;

use App\Entity\Listing;
use App\Entity\Permission;
use App\Entity\Place;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PlaceOccupationPermissionsType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'placepermissions_68351',
			'translation_domain' => 'places',
			'data_class'		=> 'App\Entity\PlacePermission',
		));
		$resolver->setRequired(['p', 'me']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$p = $options['p'];
		$me = $options['me'];
		$builder->add('occupied_place', EntityType::class, array(
			'required' => true,
			'class'=>Place::class, 'choice_label'=>'name', 'query_builder'=>function(EntityRepository $er) use ($p) {
				return $er->createQueryBuilder('p')->where('p = :p')->setParameter('p',$p);
			}
		));
		// TODO: filter according to what's available? (e.g. no permission for docks at regions with no coast)
		$builder->add('permission', EntityType::class, array(
			'required' => true,
			'choice_translation_domain' => true,
			'class'=>Permission::class,
			'choice_label'=>'translation_string',
			'query_builder'=>function(EntityRepository $er) {
				return $er->createQueryBuilder('p')->where('p.class = :class')->setParameter('class', 'place');
			}
		));
		$builder->add('value', IntegerType::class, array(
			'required' => false,
		));
		$builder->add('reserve', IntegerType::class, array(
			'required' => false,
		));
		$builder->add('listing', EntityType::class, array(
			'required' => true,
			'placeholder'=>'perm.choose',
			'class'=>Listing::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($me) {
				return $er->createQueryBuilder('l')->where('l.owner = :me')->setParameter('me',$me->getUser());
			}
		));
	}
}
