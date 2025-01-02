<?php

namespace App\Form;

use App\Entity\Character;
use App\Entity\Settlement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * Form for creating a new subrealm.
 * Accepts 'realm' as variable. Expects a Realm object.
 */
class SubrealmType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'estates_824',
			'translation_domain' => 'politics',
			'attr'					=> array('class'=>'wide')
		));
		$resolver->setRequired(['realm']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$realm = $options['realm'];
		$realmtypes = array();
		for ($i=1;$i<$realm->getType();$i++) {
			$realmtypes['realm.type.'.$i] = $i;
		}

		$builder->add('settlement', EntityType::class, array(
			'label' => 'diplomacy.subrealm.estates',
			'multiple'=>true,
			'expanded'=>true,
			'class'=>Settlement::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($realm) {
				$qb = $er->createQueryBuilder('e');
				$qb->where('e.realm = :realm')->setParameter('realm', $realm);
				$qb->orderBy('e.name');
				return $qb;
			},
		));
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
		$builder->add('type', ChoiceType::class, array(
			'required'=>true,
			'placeholder'=>'diplomacy.subrealm.empty',
			'choices' => $realmtypes,
			'label'=> 'realm.designation',
		));
		$builder->add('ruler', EntityType::class, array(
			'label' => 'diplomacy.subrealm.ruler',
			'placeholder'=>'diplomacy.subrealm.empty',
			'multiple'=>false,
			'expanded'=>false,
			'class'=>Character::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($realm) {
				$qb = $er->createQueryBuilder('c');
				$qb->join('c.owned_settlements', 's');
				$qb->where('s.realm = :realm')->setParameter('realm', $realm);
				$qb->orderBy('c.name');
				return $qb;
			},
		));
		$builder->add('submit', SubmitType::class, array('label'=>'diplomacy.subrealm.submit'));
	}
}
