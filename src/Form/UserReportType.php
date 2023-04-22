<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserReportType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'userreport_9432',
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('text', TextareaType::class, array(
			'label'=>'olympus.report.form.help',
			'attr' => array('title'=>'olympus.report.form.text'),
			'required'=>true,
		));
		$builder->add('submit', SubmitType::class, array('label'=>'submit'));
	}
}
