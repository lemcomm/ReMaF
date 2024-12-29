<?php

namespace App\Form;

use App\Entity\Realm;
use App\Entity\RealmDesignation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RealmManageType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'realmmanage_13535',
			'translation_domain' => 'politics',
			'data_class'		=> Realm::class,
		));
		$resolver->setRequired(['min','max', 'designations']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$min = $options['min']+1;
		$max = $options['max'];
		$designations = $options['designations'];
		if ($max > 0) {
			$max = $max-1;
		} else {
			$max = 7;
		}
		$builder->add('name', TextType::class, [
			'label'=>'realm.name',
			'required'=>true,
			'attr' => ['size'=>20, 'maxlength'=>40]
		]);
		$builder->add('formal_name', TextType::class, [
			'label'=>'realm.formalname',
			'required'=>true,
			'attr' => ['size'=>40, 'maxlength'=>160]
		]);
		$builder->add('colour_hex', TextType::class, [
			'label'=>'realm.colour',
			'required'=>true,
			'attr' => ['size'=>7, 'maxlength'=>7]
		]);
		$builder->add('colour_rgb', HiddenType::class);

		$builder->add('language', TextType::class, [
			'label'=>'realm.language',
			'required'=>false
		]);

		$realmtypes = array();
		for ($i=$min;$i<=$max;$i++) {
			$realmtypes['realm.type.'.$i] = $i;
		}

		$builder->add('type', ChoiceType::class, [
			'required'=>true,
			'choices' => $realmtypes,
			'label'=> 'realm.type.title',
		]);

		$builder->add('designation', EntityType::class, [
			'label' => 'realm.designation.title',
			'class' => RealmDesignation::class,
			'choices' => $designations,
			'choice_translation_domain' => true,
			'choice_label' => function(RealmDesignation $des): string {
				return 'realm.designation.'.$des->getName();
			}
		]);

		$builder->add('submit', SubmitType::class, [
			'label'=>'realm.manage.submit'
		]);
	}
}
