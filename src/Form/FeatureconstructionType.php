<?php

namespace App\Form;

use App\Entity\FeatureType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class FeatureconstructionType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'intention'     	=> 'featureconstruction_5215',
			'translation_domain' 	=> 'economy',
			'features'		=> [],
			'river'			=> false,
			'coast'			=> false
		]);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('existing', FormType::class);

		foreach ($options['features'] as $feature) {
			if (!$feature->getType()->getHidden()) {
				if ($feature->getActive()) {
					$builder->get('existing')->add(
						(string)$feature->getId(),
						TextType::class, array(
							'label'=>'feature.name',
							'data'=>$feature->getName(),
							'required'=>true,
							'empty_data'=>'(unnamed)',
							'attr' => array('size'=>20, 'maxlength'=>60)
						)
					);
				} else {
					$builder->get('existing')->add(
						(string)$feature->getId(),
						PercentType::class,
						array(
							'required' => false,
							'precision' => 2,
							'data' => $feature->getWorkers(),
							'attr' => array('size'=>3, 'class' => 'assignment')
						)
					);
				}				
			}
		}
	
		$builder->add('new', FormType::class);

		$builder->get('new')->add('workers', PercentType::class,
			array(
				'required' => false,
				'precision' => 2,
				'attr' => array('size'=>3, 'class' => 'assignment')
			)
		);
		$river = $options['river'];
		$coast = $options['coast'];
		$builder->get('new')->add('type', EntityType::class, array(
			'required'=>false,
			'placeholder'=>'feature.none',
			'class'=>FeatureType::class,
			'choice_label'=>'nametrans',
			'choice_translation_domain' => true,
			'query_builder'=>function(EntityRepository $er) use ($river, $coast) {
				$qb = $er->createQueryBuilder('t');
				$qb->where('t.hidden=false');
				if (!$river) {
					$qb->andWhere('t.name != :bridge')->setParameter('bridge', 'bridge');
				}
				// FIXME: what about large lakes?
				if (!$coast) {
					$qb->andWhere('t.name != :docks')->setParameter('docks', 'docks');
				}
				return $qb;
			}
		));
		$builder->get('new')->add('name', TextType::class, array(
			'label'=>'feature.name',
			'required'=>false,
			'empty_data'=>'(unnamed)',
			'attr' => array('size'=>20, 'maxlength'=>60)
		));

		$builder->get('new')->add('location_x', HiddenType::class, array('required'=>false));
		$builder->get('new')->add('location_y', HiddenType::class, array('required'=>false));
	}


}
