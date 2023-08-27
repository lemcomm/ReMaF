<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RealmManageType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'realmmanage_13535',
			'translation_domain' => 'politics',
			'data_class'		=> 'BM2\SiteBundle\Entity\Realm',
		));
		$resolver->setRequired(['min','max']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$min = $options['min']+1;
		$max = $options['max'];
		if ($max > 0) {
			$max = $max-1;
		} else {
			$max = 7;
		}
		$builder->add('name', TextType::class, array(
			'label'=>'realm.name',
			'required'=>true,
			'attr' => array('size'=>20, 'maxlength'=>40)
		));
		$builder->add('formal_name', TextType::class, array(
			'label'=>'realm.formalname',
			'required'=>true,
			'attr' => array('size'=>40, 'maxlength'=>160)
		));
		$builder->add('colour_hex', TextType::class, array(
			'label'=>'realm.colour',
			'required'=>true,
			'attr' => array('size'=>7, 'maxlength'=>7)
		));
		$builder->add('colour_rgb', HiddenType::class);

		$builder->add('language', TextType::class, array(
			'label'=>'realm.language',
			'required'=>false
		));

		$realmtypes = array();
		for ($i=$min;$i<=$max;$i++) {
			$realmtypes[$i] = 'realm.type.'.$i;
		}

		$builder->add('type', ChoiceType::class, array(
			'required'=>true,
			'choices' => $realmtypes,
			'label'=> 'realm.designation',
		));

		$builder->add('submit', SubmitType::class, array(
			'label'=>'realm.manage.submit'
		));
	}
}
