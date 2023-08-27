<?php

namespace App\Form;

use App\Entity\Realm;
use App\Entity\RealmRelation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class RealmRelationType extends AbstractType {
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'realmrelation_5414',
			'data_class'		=> RealmRelation::class,
			'translation_domain' 	=> 'politics',
			'attr'			=> array('class'=>'wide')
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('target_realm', EntityType::class, array(
			'placeholder' => 'diplomacy.relations.emptytarget',
			'label'=>'diplomacy.relations.target',
			'class'=>Realm::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) {
				$qb = $er->createQueryBuilder('r');
				$qb->orderBy('r.name', 'ASC');
				return $qb;
			}
		));
		$statuses = array(
			'nemesis', 'war', 'peace', 'friend', 'ally'
		);

		$choices = array();
		foreach ($statuses as $status) {
			$choices[$status] = 'diplomacy.status.'.$status;
		}

		$builder->add('status', ChoiceType::class, array(
			'placeholder' => 'diplomacy.relations.emptystatus',
			'label'=>'diplomacy.status.name',
			'required'=>true,
			'choices'=>$choices
		));
		$builder->add('public', TextareaType::class, array(
			'label'=>'diplomacy.relations.public',
			'trim'=>true,
			'required'=>true
		));
		$builder->add('internal', TextareaType::class, array(
			'label'=>'diplomacy.relations.internal',
			'trim'=>true,
			'required'=>true
		));
		$builder->add('delivered', TextareaType::class, array(
			'label'=>'diplomacy.relations.delivered',
			'trim'=>true,
			'required'=>true
		));

		$builder->add('submit', SubmitType::class, array('label'=>'diplomacy.relations.submit'));
	}
}
