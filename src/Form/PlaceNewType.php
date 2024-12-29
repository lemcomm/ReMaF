<?php

namespace App\Form;

use App\Entity\PlaceType;
use App\Entity\Realm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceNewType extends AbstractType {
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'newplace_1337',
			'translation_domain' => 'places'
		));
		$resolver->setRequired(['types', 'realms']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$types = $options['types'];
		$realms = $options['realms'];
		$builder->add('name', TextType::class, array(
			'label'=>'names.name',
			'required'=>true,
			'attr' => array(
				'size'=>20,
				'maxlength'=>40,
				'title'=>'help.new.name'
			)
		));
		$builder->add('formal_name', TextType::class, array(
			'label'=>'names.formalname',
			'required'=>true,
			'attr' => array(
				'size'=>40,
				'maxlength'=>160,
				'title'=>'help.new.formalname'
			)
		));
		$builder->add('type', EntityType::class, array(
			'label'=>'type.label',
			'required'=>true,
			'placeholder' => 'type.empty',
			'attr' => array('title'=>'help.new.type'),
			'class' => PlaceType::class,
			'choice_translation_domain' => true,
			'choice_label' => 'name',
			'choices' => $types,
			'group_by' => function($val, $key, $index) {
				if ($val->getRequires() == NULL) {
					return 'by.none';
				} elseif (in_array($val->getRequires(), ['inn', 'library', 'tavern', 'castle', 'fort', 'docks', 'track', 'arena', 'academy', 'warehouse', 'temple', 'list field', 'tournament', 'smith'])) {
					return 'by.building';
				} else {
					return 'by.'.$val->getRequires();
				}
			}
		));
		$builder->add('realm', EntityType::class, array(
			'label'=>'realm.label',
			'required'=>false,
			'placeholder' => 'realm.empty',
			'attr' => array('title'=>'help.new.realm'),
			'class' => Realm::class,
			'choice_translation_domain' => true,
			'choice_label' => 'name',
			'choices' => $realms
		));
		$builder->add('short_description', TextareaType::class, array(
			'label'=>'description.short',
			'attr' => array('title'=>'help.new.shortdesc'),
			'required'=>true,
		));
		$builder->add('description', TextareaType::class, array(
			'label'=>'description.full',
			'attr' => array('title'=>'help.new.longdesc'),
			'required'=>true,
		));
	}
}
