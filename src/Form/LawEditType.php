<?php

namespace App\Form;

use App\Entity\Association;
use App\Entity\Settlement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LawEditType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'lawedit_43230',
			'translation_domain' 	=> 'orgs',
			'law'			=> null,
			'choices'		=> [],
			'settlements'		=> [],
			'faiths'		=> [],
		));
		$resolver->setRequired(['type']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$type = $options['type']->getName();
		$law = $options['law'];
		$choices = $options['choices'];
		$taxes = ['taxesFood', 'taxesWood', 'taxesMetal', 'taxesWealth'];

		if($type == 'freeform') {
			$builder->add('title', TextType::class, array(
				'label'=>'law.form.edit.title',
				'data'=>$law?$law->getTitle():null,
				'required'=>true
			));
			$builder->add('description', TextareaType::class, array(
				'label'=>'law.form.edit.desc',
				'data'=>$law?$law->getDescription():null,
				'required'=>true
			));
		} else {
			$builder->add('title', HiddenType::class, array(
				'data'=>false
			));
			$builder->add('description', HiddenType::class, array(
				'data'=>false
			));
		}
		$builder->add('mandatory', ChoiceType::class, array(
			'label'=>'law.form.edit.mandatory.label',
			'required'=>false,
			'data'=>$law?$law->getMandatory():true,
			'choices'=>[
				'yes' => true,
				'no' => false
				],
			'choice_translation_domain'=>'orgs',
			'choice_label'=>function($choice, $key, $value) {
				if($key == 'yes') {
					return 'law.form.edit.mandatory.yes';
				} else {
					return 'law.form.edit.mandatory.no';
				}
			},
		));
		$builder->add('cascades', CheckboxType::class, array(
			'label'=>'law.form.edit.cascades',
			'data'=>$law?$law->getCascades():false,
			'required'=>false,
		));
		if ($type !== 'freeform') {
			if ($type === 'realmFaith') {
				$builder->add('value', ChoiceType::class, array(
					'label'=>'law.form.edit.value',
					'required'=>true,
					'choices'=>$choices[$type],
					'placeholder'=>'law.form.type.empty',
					'choice_translation_domain'=>'orgs',
					'choice_label'=>function($choice, $key, $value) {
						return 'law.info.'.$choice.'.desc';
					},
					'data'=>$law?$law->getValue():null
				));
				$builder->add('settlement', HiddenType::class, array(
					'data'=>null
				));
				$builder->add('faith', EntityType::class, array(
					'label' => 'law.form.edit.faith',
					'multiple'=>false,
					'expanded'=>false,
					'required'=>true,
					'class'=>Association::class,
					'choice_label'=>function($choice, $key, $value) {
						return $choice->getFaithName().' ('.$choice->getName().')';
					},
					'choices'=>$options['faiths'],
					'data'=>$law?$law->getFaith():null,
				));
			} elseif ($type === 'realmVotingAge') {
				$builder->add('value', TextType::class, array(
					'label'=>'law.form.edit.amount',
					'data'=>$law?$law->getValue():null,
					'required'=>true
				));
				$builder->add('settlement', HiddenType::class, array(
					'data'=>null
				));
				$builder->add('faith', HiddenType::class, array(
					'data'=>null
				));
			} elseif (!in_array($type, $taxes)) {
				$builder->add('value', ChoiceType::class, array(
					'label'=>'law.form.edit.value',
					'required'=>true,
					'choices'=>$choices[$type],
					'placeholder'=>'law.form.type.empty',
					'choice_translation_domain'=>'orgs',
					'choice_label'=>function($choice, $key, $value) {
						return 'law.info.'.$choice.'.desc';
					},
					'data'=>$law?$law->getValue():null
				));
				$builder->add('settlement', HiddenType::class, array(
					'data'=>null
				));
				$builder->add('faith', HiddenType::class, array(
					'data'=>null
				));
			} else {
				$builder->add('value', TextType::class, array(
					'label'=>'law.form.edit.amount',
					'data'=>$law?$law->getValue():null,
					'required'=>true
				));
				$builder->add('settlement', EntityType::class, array(
					'label' => 'law.form.edit.settlement',
					'multiple'=>false,
					'expanded'=>false,
					'required'=>true,
					'class'=>Settlement::class,
					'choice_label'=>'name',
					'choices'=>$options['settlements'],
					'data'=>$law?$law->getSettlement():null,
				));
				$builder->add('faith', HiddenType::class, array(
					'data'=>null
				));
			}
		} else {
			$builder->add('value', HiddenType::class, array(
				'data'=>false
			));
			$builder->add('settlement', HiddenType::class, array(
				'data'=>null
			));
			$builder->add('faith', HiddenType::class, array(
				'data'=>null
			));
		}
		# For later when we add this in fully.
		$builder->add('sol', HiddenType::class, array(
			'data'=>false
		));
		$builder->add('submit', SubmitType::class, array('label'=>'law.form.submit'));
	}
}
