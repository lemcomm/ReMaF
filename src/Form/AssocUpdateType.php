<?php

namespace App\Form;

use App\Entity\Association;
use App\Entity\AssociationType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssocUpdateType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'updateassoc_1779',
			'translation_domain' => 'orgs'
		));
		$resolver->setRequired(['types', 'assocs', 'me']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$types = $options['types'];
		$assocs = $options['assocs'];
		$me = $options['me'];
		$builder->add('name', TextType::class, array(
			'label'=>'assoc.form.new.name',
			'required'=>true,
			'attr' => array(
				'size'=>20,
				'maxlength'=>40,
				'title'=>'assoc.help.name'
			),
			'data' => $me->getName()
		));
		$builder->add('formal_name', TextType::class, array(
			'label'=>'assoc.form.new.formalname',
			'required'=>true,
			'attr' => array(
				'size'=>40,
				'maxlength'=>160,
				'title'=>'assoc.help.formalname'
			),
			'data' => $me->getFormalName()
		));
		$builder->add('faith_name', TextType::class, array(
			'label'=>'assoc.form.new.faithname',
			'required'=>false,
			'attr' => array(
				'size'=>80,
				'maxlength'=>255,
				'title'=>'assoc.help.faithname'
			),
			'data' => $me->getFaithName()
		));
		$builder->add('follower_name', TextType::class, array(
			'label'=>'assoc.form.new.followername',
			'required'=>false,
			'attr' => array(
				'size'=>80,
				'maxlength'=>255,
				'title'=>'assoc.help.followername'
			),
			'data' => $me->getFollowerName()
		));
		$builder->add('type', EntityType::class, array(
			'label'=>'assoc.form.new.type',
			'required'=>true,
			'placeholder' => 'assoc.form.select',
			'attr' => array('title'=>'assoc.help.type'),
			'class' => AssociationType::class,
			'choice_translation_domain' => true,
			'choice_label' => 'name',
			'choices' => $types,
			'data' => $me?$me->getType():null
		));
		$builder->add('motto', TextType::class, array(
			'label'=>'assoc.form.new.motto',
			'required'=>false,
			'attr' => array(
				'size'=>40,
				'maxlength'=>160,
				'title'=>'assoc.help.motto'
			),
			'data' => $me?$me->getMotto():null
		));
		$builder->add('short_description', TextareaType::class, array(
			'label'=>'assoc.form.description.short',
			'attr' => array('title'=>'assoc.help.shortdesc'),
			'required'=>true,
			'data'=>$me->getShortDescription()
		));
		$builder->add('description', TextareaType::class, array(
			'label'=>'assoc.form.description.full',
			'attr' => array('title'=>'assoc.help.longdesc'),
			'required'=>true,
			'data'=>$me->getDescription()->getText()
		));
		$builder->add('superior', EntityType::class, array(
			'label'=>'assoc.form.new.superior',
			'required'=>false,
			'placeholder' => 'assoc.form.superior',
			'attr' => array('title'=>'assoc.help.type'),
			'class' => Association::class,
			'choice_translation_domain' => true,
			'choice_label' => 'name',
			'choices' => $assocs,
			'data'=>$me->getSuperior()
		));
		$builder->add('submit', SubmitType::class, array('label'=>'assoc.form.submit'));
	}
}
