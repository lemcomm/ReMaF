<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\EquipmentType;

class CharacterLoadoutType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'characterloadout_19',
			'translation_domain' => 'settings',
			'weapons'		=> [],
			'armor'			=> [],
			'equipment'		=> [],
			'mounts'		=> [],
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
                $builder->add('weapon', EntityType::class, array(
                        'label'=>'loadout.weapon',
                        'placeholder'=>'loadout.none',
                        'required'=>False,
                        'choice_label'=>'nameTrans',
                        'choice_translation_domain' => 'messages',
                        'class'=>EquipmentType::class,
                        'choices'=>$options['weapons']
                ));
                $builder->add('armour', EntityType::class, array(
                        'label'=>'loadout.armor',
                        'placeholder'=>'loadout.none',
                        'required'=>False,
                        'choice_label'=>'nameTrans',
                        'choice_translation_domain' => 'messages',
                        'class'=>EquipmentType::class,
                        'choices'=>$options['armor']
                ));
                $builder->add('equipment', EntityType::class, array(
                        'label'=>'loadout.equipment',
                        'placeholder'=>'loadout.none',
                        'required'=>False,
                        'choice_label'=>'nameTrans',
                        'choice_translation_domain' => 'messages',
                        'class'=>EquipmentType::class,
                        'choices'=>$options['equipment']
                ));
                $builder->add('mount', EntityType::class, array(
                        'label'=>'loadout.mount',
                        'placeholder'=>'loadout.none',
                        'required'=>False,
                        'choice_label'=>'nameTrans',
                        'choice_translation_domain' => 'messages',
                        'class'=>EquipmentType::class,
                        'choices'=>$options['mounts']
                ));

		$builder->add('submit', SubmitType::class, array('label'=>'button.submit'));
	}

}
