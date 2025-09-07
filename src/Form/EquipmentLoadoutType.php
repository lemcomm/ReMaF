<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\EquipmentType;

class EquipmentLoadoutType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'equipmentselect4321'
		));
		$resolver->setRequired(['opts', 'labels', 'domain']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$domain = $options['domain'];
		$opts = $options['opts'];
		$labels = $options['labels'];
                $builder->add('equipment', EntityType::class, array(
                        'label'=>$labels,
                        'placeholder'=>'loadout.none',
                        'required'=>true,
			'translation_domain'=>$domain,
                        'choice_label'=>'nameTrans',
                        'choice_translation_domain' => 'messages',
                        'class'=>EquipmentType::class,
                        'choices'=>$opts
                ));

		$builder->add('submit', SubmitType::class, array(
			'label'=>'button.submit',
			'translation_domain'=>$domain
		));
	}
}
