<?php

namespace App\Form;

use App\Entity\Character;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class CharacterCreationType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'newchar_482',
			'attr'		=> array('class'=>'wide'),
			'user'		=> null,
			'slotsavailable'=> 0
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$user = $options['user'];
		$builder->add('name', TextType::class, array(
			'label'=>'character.name',
			'required'=>true,
			'attr' => array('size'=>30, 'maxlength'=>80, 'title'=>'newcharacter.help.name')
		));
		$builder->add('gender', ChoiceType::class, array(
			'label'=>'character.gender',
			'required'=>true,
			'choices'=>array('male' => 'm', 'female' => 'f'),
			'attr' => array('title'=>'newcharacter.help.gender'),
			'choice_translation_domain' => true,
		));

		$builder->add('father', EntityType::class, array(
			'label'=>'character.father',
			'required'=>false,
			'placeholder'=>'character.none',
			'attr' => array('title'=>'newcharacter.help.father'),
			'class'=>Character::class, 'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($user) {
				return $er->createQueryBuilder('c')
					->leftJoin('c.partnerships', 'm', 'WITH', '(m.with_sex IS NULL OR (m.with_sex=true AND m.active=true))')->leftJoin('m.partners', 'p', 'WITH', 'p.id != c.id')
					->where('(c.user = :user OR p.user = :user)')->andWhere('c.male = true')->andWhere('c.npc = false')->orderBy('c.name')
					->setParameters(array('user'=>$user));
		}));
		$builder->add('mother', EntityType::class, array(
			'label'=>'character.mother',
			'required'=>false,
			'placeholder'=>'character.none',
			'attr' => array('title'=>'newcharacter.help.mother'),
			'class'=>Character::class, 'choice_label'=>'name', 'query_builder'=>function(EntityRepository $er) use ($user) {
				return $er->createQueryBuilder('c')
					->leftJoin('c.partnerships', 'm', 'WITH', '(m.with_sex IS NULL OR (m.with_sex=true AND m.active=true))')->leftJoin('m.partners', 'p', 'WITH', 'p.id != c.id')
					->where('(c.user = :user OR p.user = :user)')->andWhere('c.male = false')->andWhere('c.npc = false')->orderBy('c.name')
					->setParameters(array('user'=>$user));
		}));

		$builder->add('partner', EntityType::class, array(
			'label'=>'character.married',
			'required'=>false,
			'placeholder'=>'character.none',
			'attr' => array('title'=>'newcharacter.help.partner'),
			'class'=>Character::class, 'choice_label'=>'name', 'query_builder'=>function(EntityRepository $er) use ($user) {
				return $er->createQueryBuilder('c')->where('c.user = :user')->andWhere('c.npc = false')->orderBy('c.name')->setParameters(array('user'=>$user));
		}));

		if ($options['slotsavailable']) {
			$builder->add('dead', CheckboxType::class, array(
				'label' => 'dead',
				'required' => false,
				'empty_data' => false,
				'attr' => array('title'=>'newcharacter.help.dead'),
			));
		} else {
			$builder->add('dead', CheckboxType::class, array(
				'label' => 'dead',
				'required' => true,
				'attr' => array('title'=>'newcharacter.help.dead', 'checked'=>'checked', 'disabled'=>'disabled'),
			));
		}

		$builder->add('submit', SubmitType::class, array('label'=>'newcharacter.submit'));
	}
}
