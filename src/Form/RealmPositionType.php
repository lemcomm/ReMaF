<?php

namespace App\Form;

use App\Entity\PositionType;
use App\Entity\RealmPosition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class RealmPositionType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'realmpositions_461234',
			'translation_domain' 	=> 'politics',
			'data_class'		=> RealmPosition::class,
			'attr'			=> array('class'=>'wide')
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$pos = $builder->getData();
		if ($pos->getRuler()) {
			$is_ruler = true;
		} else {
			$is_ruler = false;
		}

		$builder->add('name', TextType::class, array(
			'label'=>'position.name',
			'required'=>true,
			'attr' => array('size'=>20, 'maxlength'=>40)
		));
		$builder->add('description', TextareaType::class, array(
			'label'=>'position.description',
			'required'=>true,
		));
		if (!$is_ruler) {
			/*$builder->add('permissions', EntityType::class', array(
				'label'=>'position.permissions',
				'required' => false,
				'multiple' => true,
				'expanded' => true,
				'choice_translation_domain' => true,
				'class'=>Permission::class,
				'choice_label'=>'translation_string',
				'query_builder'=>function(EntityRepository $er) {
					return $er->createQueryBuilder('p')->where('p.class = :class')->setParameter('class', 'realm');
				}
			));*/
			$builder->add('type', EntityType::class, array(
				'label' => 'position.type',
				'required' => false,
				'placeholder' => 'position.help.none',
				'attr' => array('title'=>'position.help.type'),
				'class' => PositionType::class,
				'choice_translation_domain' => true,
				'choice_label' => 'name',
				'query_builder' => function(EntityRepository $er) {
					return $er->createQueryBuilder('p')->where('p.id > 0')->orderBy('p.name');
				}
			));
			/*$builder->add('rank', NumberType::class, array(
				'label'=>'position.rank',
				'required' => false,
				'empty_data' => '1',
				'attr' => array('title'=>'position.help.rank'),
			));*/
			$builder->add('retired', CheckboxType::class, array(
				'label'=>'position.retired',
				'required' => false,
				'attr' => array('title'=>'position.help.retired'),
			));
			$builder->add('legislative', CheckboxType::class, array(
				'label'=>'position.legislative',
				'required' => false,
				'attr' => array('title'=>'position.help.legislative'),
			));
			$builder->add('have_vassals', CheckboxType::class, array(
				'label'=>'position.haveVassals',
				'required' => false,
				'attr' => array('title'=>'position.help.haveVassals'),
			));
		}
		$builder->add('minholders', IntegerType::class, array(
			'label'=>'position.minholders',
			'scale'=>0,
			'required' => false,
			'empty_data' => '1',
			'attr' => array('title'=>'position.help.minholders'),
		));
		$builder->add('inherit', CheckboxType::class, array(
			'label'=>'position.inherit',
			'required' => false,
			'attr' => array('title'=>'position.help.inherit'),
		));
		if (!$is_ruler) {
			$builder->add('elected', CheckboxType::class, array(
				'label'=>'position.elected',
				'required' => false,
				'attr' => array('title'=>'position.help.elected'),
			));
		}
		$builder->add('electiontype', ChoiceType::class, array(
			'label'=>'elections.method.name',
			'placeholder'=>'elections.method.empty',
			'choice_translation_domain' => true,
			'required' => false,
			'choices' => array(
				'banner' => 'elections.method.banner',
				'spears' => 'elections.method.spears',
				'swords' => 'elections.method.swords',
				'horses' => 'elections.method.horses',
				'land'	=> 'elections.method.land',
				'realmland' => 'elections.method.realmland',
				'castles' => 'elections.method.castles',
				'realmcastles' => 'elections.method.realmcastles',
				'heads'	=> 'elections.method.heads',
			),
			'attr' => array('title'=>'position.help.electiontype'),
		));
		$builder->add('term', ChoiceType::class, array(
			'label'=>'position.term',
			'choices' => array(
				0 => 'position.terms.0',
				365 => 'position.terms.365',
				90 => 'position.terms.90',
				30 => 'position.terms.30',
			),
			'attr' => array('title'=>'position.help.term'),
		));
		$builder->add('year', IntegerType::class, array(
			'label'=>'position.year',
			'scale'=>0,
			'required' => false,
			'empty_data' => '1',
			'attr' => array('title'=>'position.help.year'),
		));
		$builder->add('week', IntegerType::class, array(
			'label'=>'position.week',
			'scale'=>0,
			'required' => false,
			'empty_data' => '1',
			'attr' => array('title'=>'position.help.week'),
		));
		if (!$is_ruler) {
			$builder->add('keeponslumber', CheckboxType::class, array(
				'label'=>'position.keeponslumber',
				'required' => false,
				'attr' => array('title'=>'position.help.keeponslumber'),
			));
		}
		$builder->add('submit', SubmitType::class, array('label'=>'position.submit'));
	}
}
