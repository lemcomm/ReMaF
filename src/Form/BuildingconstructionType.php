<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class BuildingconstructionType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults([
			'intention'       => 'buildingconstruction_144',
			'existing'	=> [],
			'available'	=> []
		]);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('existing', FormType::class);
		foreach ($options['existing'] as $existing) {
			$builder->get('existing')->add(
				(string)$existing->getId(),
				PercentType::class,
				array(
					'required' => false,
					'scale' => 2,
					'data' => $existing->getWorkers(),
					'attr' => array('size'=>3, 'class' => 'assignment')
				)
			);
		}

		$builder->add('available', FormType::class);
		foreach ($options['available'] as $available) {
			$builder->get('available')->add(
				(string)$available['id'],
				PercentType::class,
				array(
					'required' => false,
					'scale' => 2,
					'attr' => array('size'=>3, 'class' => 'assignment')
				)
			);
		}
	}


}
