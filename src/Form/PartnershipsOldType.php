<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartnershipsOldType extends AbstractType {


	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'partnership_5712',
			'translation_domain' => 'politics'
		));
		$resolver->setRequired(['me', 'others']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$this->buildFormOld($builder, $options);
	}

	public function buildFormOld(FormBuilderInterface $builder, array $options) {
		$builder->add('partnership', FormType::class);

		foreach ($options['others'] as $partnership) {
			if ($partnership->getActive()) {
				$label = 'relation.choice.change';
				$choices=array();
				if (!$partnership->getPublic()) {
					$choices['relation.choice.makepublic'] = 'public';
				}
				if ($partnership->getWithSex()) {
					$choices['relation.choice.refusesex'] = 'nosex';
				}
				$choices['relation.choice.cancel'] = 'cancel';
			} else {
				if ($partnership->getInitiator() == $options['me']) {
					$label = 'relation.choice.stay';
					$choices = array(
						'relation.choice.withdraw' => 'withdraw'
					);
				} else {
					$label = 'relation.choice.decide';
					$choices = array(
						'relation.choice.accept' => 'accept',
						'relation.choice.reject' => 'reject'
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
		$builder->add('submit', SubmitType::class, [
			'label' => 'relation.choice.submitchange'
		]);
	}
}
