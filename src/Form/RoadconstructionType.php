<?php

namespace App\Form;

use App\Entity\GeoFeature;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class RoadconstructionType extends AbstractType {


	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'roadconstruction_0345',
			'translation_domain' 	=> 'actions',
			'roads'			=> []
		));
		$resolver->setRequired(['settlement']);
	}

// FIXME: why don't I go on the entity here and access the workers variable directly ??

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('existing', FormType::class);

		foreach ($options['roads'] as $data) {
			// max road level: 5
			if ($data['road']->getQuality()>=5) {
				$disabled = true;
			} else {
				$disabled = false;
			}
			$builder->get('existing')->add(
				(string)$data['road']->getId(),
				PercentType::class,
				array(
					'required' => false,
					'disabled' => $disabled,
					'scale' => 2,
					'data' => $data['road']->getWorkers(),
					'attr' => array('size'=>3, 'class' => 'assignment')
				)
			);
		}

		$builder->add('new', FormType::class);

		$builder->get('new')->add('workers', PercentType::class,
			array(
				'required' => false,
				'scale' => 2,
				'attr' => array('size'=>3, 'class' => 'assignment')
			)
		);
		$geo = $options['settlement']->getGeoData();
		// yes, these are different. the first ensures that at least one of the features belongs to your region
		$builder->get('new')->add('from', EntityType::class, array(
			'label'=>'economy.roads.from',
			'required'=>false,
			'placeholder'=>'form.choose',
			'choice_translation_domain' => true,
			'class'=>GeoFeature::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($geo) {
				return $er->createQueryBuilder('f')->where('f.geo_data = :geo')->orderBy('f.name')->setParameters(array('geo'=>$geo));
			}
		));
		// 100m or so beyond the border, to include border posts, etc. - hardcoded value for now
		$builder->get('new')->add('to', EntityType::class, array(
			'label'=>'economy.roads.to',
			'required'=>false,
			'placeholder'=>'form.choose',
			'choice_translation_domain' => true,
			'class'=>GeoFeature::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($geo) {
				return $er->createQueryBuilder('f')->from('App:GeoData', 'g')
					->where('ST_Distance(g.poly, f.location) < :gutter')
					->andWhere('g = :geo')->orderBy('f.name')->setParameters(array('geo'=>$geo, 'gutter'=>100));
			}
		));

	}


}
