<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class EntourageRecruitType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'recruit_23469',
			'translation_domain' => 'actions',
		));
		$resolver->setRequired('entourage');
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('recruits', FormType::class);
		$entourage = $options['entourage'];

		foreach ($entourage as $recruit) {
			$builder->get('recruits')->add(
				(string)$recruit['type']->getId(),
				IntegerType::class,
				array(
					'required' => false,
					'attr' => array(
						'size'=>3, 'maxlength'=>3,
						'min'=>0,
						'class'=>'recruitment',
						'data-time'=>method_exists($recruit['type'], 'getTrainingRequired')?$recruit['type']->getTrainingRequired():0
					)
				)
			);
		}
	}


}
