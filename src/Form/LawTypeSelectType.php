<?php

namespace App\Form;

use App\Entity\LawType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LawTypeSelectType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'law_313375',
			'translation_domain' => 'orgs'
		));
		$resolver->setRequired(['types']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {

		$builder->add('target', EntityType::class, array(
			'placeholder' => 'law.form.type.empty',
			'label' => 'law.form.type.label',
			'required'=>true,
			'attr' => array('title'=>'laws.help.types'),
			'class'=>LawType::class,
			'choice_translation_domain'=>'orgs',
			'choice_label'=>function($choice, $key, $value) {
				return 'law.info.'.$choice->getName().'.label';
			},
			'choices'=>$options['types']
		));

		$builder->add('submit', SubmitType::class, array('label'=>'law.form.submit'));
	}


}
