<?php

namespace App\Form;

use App\Entity\Election;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ElectionType extends AbstractType {
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'intention'       	=> 'election_23865',
			'translation_domain' 	=> 'politics',
			'data_class'		=> Election::class,
			'attr'			=> ['class'=>'wide']
		]);
	}
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', TextType::class, [
			'label'=>'elections.title',
			'required'=>true,
			'attr' => ['size'=>20, 'maxlength'=>40]
		]);
		$builder->add('description', TextareaType::class, [
			'label'=>'elections.desc',
			'required'=>true,
		]);
		$builder->add('method', ChoiceType::class, [
			'label'=>'elections.method.name',
			'placeholder'=>'elections.method.empty',
			'choice_translation_domain' => true,
			'choices' => [
				'elections.method.banner' => 'banner',
				'elections.method.spears' => 'spears',
				'elections.method.swords' => 'swords',
				'elections.method.horses' => 'horses',
				'elections.method.land' => 'land',
				'elections.method.realmland' => 'realmland',
				'elections.method.castles' => 'castles',
				'elections.method.realmcastles' => 'realmcastles',
				'elections.method.heads' => 'heads',
			]
		]);
		$builder->add('duration', ChoiceType::class, [
			'label'=>'elections.duration.name',
			'placeholder'=>'elections.duration.empty',
			'mapped'=>false,
			'choice_translation_domain' => true,
			'choices' => [
				'elections.duration.1' => 1,
				'elections.duration.3' => 3,
				'elections.duration.5' => 5,
				'elections.duration.7' => 7,
				'elections.duration.10' => 10,
			]
		]);

		$builder->add('submit', SubmitType::class, ['label'=>'elections.submit']);
	}
}
