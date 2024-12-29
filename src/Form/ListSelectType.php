<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ListSelectType extends AbstractType {

	private array $character_groups = array(1,2,3,4, 21,22,23,29, 41,42,43,44,45,46,47, 71);

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention' => 'listselect_19273',
			'attr'		=> array('class'=>'tall')
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$groups = array();
		foreach ($this->character_groups as $id) {
			#$groups[$id] = 'character.list.'.$id;
			$groups['character.list.'.$id] = $id;
		}
		$builder->add('char', HiddenType::class);
		$builder->add('list', ChoiceType::class, array(
			'label'=>'character.list.select',
			'expanded'=>true,
			'required'=>true,
			'choices'=>$groups,
			'translation_domain'=>'messages'
		));
		$builder->add('submit', SubmitType::class, array('label'=>'character.list.submit'));
	}

}
