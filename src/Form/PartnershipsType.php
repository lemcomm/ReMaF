<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartnershipsType extends AbstractType {


	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'partnership_5712',
			'translation_domain' => 'politics'
		));
		$resolver->setRequired(['me', 'newpartners', 'others']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		if ($options['newpartners']) {
			$this->buildFormNew($builder, $options);
		} else {
			$this->buildFormOld($builder, $options);
		}
	}

	public function buildFormNew(FormBuilderInterface $builder, array $options) {
		$types = array(
			'liason' => 'relation.choice.liason',
			'engagement' => 'relation.choice.engagement',
			'paramour' => 'relation.choice.paramour',
			'marriage' => 'relation.choice.marriage'
		);
		$builder->add('type', ChoiceType::class, array(
			'choices' => $types,
			'label' => 'relation.choice.type',
			'placeholder' => 'relation.choice.choose',
		));
		$builder->add('partner', ChoiceType::class, array(
			'choices' => $options['others'],
			'label' => 'relation.choice.partner',
			'placeholder' => 'relation.choice.choose',
		));
		$builder->add('public', CheckboxType::class, array(
			'label' => 'relation.choice.public',
			'required' => false,
		));
		$builder->add('sex', CheckboxType::class, array(
			'label' => 'relation.choice.sex',
			'required' => false,
		));
		$builder->add('crest', CheckboxType::class, array(
			'label' => 'relation.choice.crest',
			'required' => false,
		));
	}

	public function buildFormOld(FormBuilderInterface $builder, array $options) {
		$builder->add('partnership', FormType::class);

		foreach ($options['others'] as $partnership) {
			if ($partnership->getActive()) {
				$label = 'relation.choice.change';
				$choices=array();
				if (!$partnership->getPublic()) {
					$choices['public'] = 'relation.choice.makepublic';
				}
				if ($partnership->getWithSex()) {
					$choices['nosex'] = 'relation.choice.refusesex';
				}
				$choices['cancel'] = 'relation.choice.cancel';
			} else {
				if ($partnership->getInitiator() == $options['me']) {
					$label = 'relation.choice.stay';
					$choices = array(
						'withdraw' => 'relation.choice.withdraw'
					);
				} else {
					$label = 'relation.choice.decide';
					$choices = array(
						'accept' => 'relation.choice.accept',
						'reject' => 'relation.choice.reject'
					);					
				}
			}
			$builder->get('partnership')->add(
				(string)$partnership->getId(), ChoiceType::class, array(
					'choices' => $choices,
					'label' => $label,
					'placeholder' => 'relation.choice.nochange',
					'required' => false,
				)
			);
		}
	}
}
