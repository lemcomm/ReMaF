<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AssocSelectType extends AbstractType {

	public function getName() {
		return 'assoc';
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'     => 'asoc_90210',
			'assocs'	=> [],
			'type'		=> 'faith',
			'me'		=> null
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$assocs = $options['assocs'];
		switch ($options['type']) {
			case 'faith':
				$empty		= 'assoc.form.faith.empty';
				$label		= 'assoc.form.faith.name';
				$submit		= 'assoc.form.submit';
				$msg    	= null;
				$domain		= 'orgs';
				$help		= 'assoc.help.faith';
				$choiceLabel	= 'faith_name';
				$required 	= false;
				break;
			case 'addToPlace':
				$empty		= 'assoc.form.addToPlace.empty';
				$label		= 'assoc.form.addToPlace.name';
				$submit		= 'assoc.form.submit';
				$msg    	= null;
				$domain		= 'orgs';
				$help		= 'assoc.help.addToPlace';
				$choiceLabel	= 'name';
				$required 	= true;
				break;
		}
		$me = $options['me'];

		$builder->add('target', EntityType::class, array(
			'placeholder' => $empty,
			'label' => $label,
			'required'=>$required,
			'attr' => array('title'=>$help),
			'class'=>'BM2SiteBundle:Association',
			'choice_label'=>$choiceLabel,
			'choices'=>$assocs,
			'data'=>$me->getFaith()
		));
		if ($this->msg !== null) {
			$builder->add('message', TextareaType::class, [
				'label' => $msg,
				'translation_domain'=>$domain,
				'required' => true
			]);
		}

		$builder->add('submit', SubmitType::class, array('label'=>$submit));
	}


}
