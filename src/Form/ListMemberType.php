<?php

namespace App\Form;

use App\Entity\ListMember;
use App\DataTransformer\CharacterTransformer;
use App\DataTransformer\RealmTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use App\Entity\Listing;

class ListMemberType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'translation_domain' => 'politics',
			'data_class'		=> ListMember::class,
		));
		$resolver->setRequired(['em', 'listing']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$l = $options['listing'];
		if ($l) {
			if ($l->getId()>0) {
				$builder->add('listing', EntityType::class, array(
					'required' => true,
					'class'=>Listing::class,
					'choice_label'=>'id',
					'query_builder'=>function(EntityRepository $er) use ($l) {
						return $er->createQueryBuilder('l')->where('l = :l')->setParameter('l',$l);
					}
				));
			}
			$builder->add('priority', IntegerType::class, array(
				'required' => true,
			));
			$builder->add('allowed', CheckboxType::class, array(
				'required' => false,
			));
		}
		$builder->add('includeSubs', CheckboxType::class, array(
			'required' => false,
		));

		$realm_transformer = new RealmTransformer($options['em']);
		$builder->add(
			$builder->create('target_realm', TextType::class, array(
			'required' => false,
			'attr'=>array('class'=>'realmselect'),
			))->addModelTransformer($realm_transformer)
		);

		$char_transformer = new CharacterTransformer($options['em']);
		$builder->add(
			$builder->create('target_character', TextType::class, array(
			'required' => false,
			'attr'=>array('class'=>'charselect'),
			))->addModelTransformer($char_transformer)
		);

		if ($l == null) {
			$builder->add('submit', SubmitType::class);
		}
	}
}
