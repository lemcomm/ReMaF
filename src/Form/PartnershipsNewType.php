<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartnershipsNewType extends AbstractType {


	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'partnership_5712',
			'translation_domain' => 'politics'
		));
		$resolver->setRequired(['others']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$this->buildFormNew($builder, $options);
	}

	public function buildFormNew(FormBuilderInterface $builder, array $options): void {
		$types = array(
			'relation.choice.liason' => 'liason',
			'relation.choice.engagement' => 'engagement',
			'relation.choice.paramour' => 'paramour',
			'relation.choice.marriage' => 'marriage'
		);
		$builder->add('type', ChoiceType::class, array(
			'choices' => $types,
			'label' => 'relation.choice.type',
			'placeholder' => 'relation.choice.choose',
		));
		$builder->add('partner', ChoiceType::class, array(
			'choices' => $options['others'],
			'label' => 'relation.choice.partner',
			'choice_label' => 'name',
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
		$builder->add('submit', SubmitType::class, [
			'label' => 'relation.choice.submitnew'
		]);
	}
}
