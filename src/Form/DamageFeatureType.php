<?php

namespace App\Form;

use App\Entity\GeoFeature;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * Form for damaging region features.
 *
 * Accepts the following options (in their legacy order):
 * * 'features' - array - Features that can be damaged.
 */
class DamageFeatureType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'damagefeatures_9615',
			'translation_domain' => 'actions'
		));
		$resolver->setRequired(['features']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$features = $options['features'];
		$builder->add('target', EntityType::class, array(
			'label'=>'military.damage.target',
			'expanded'=>true,
			'class'=>GeoFeature::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($features) {
				return $er->createQueryBuilder('f')->where('f IN (:features)')->orderBy('f.name')->setParameters(array('features'=>$features));
		}));

		$builder->add('submit', 'submit', array('label'=>'military.damage.submit'));
	}


}
