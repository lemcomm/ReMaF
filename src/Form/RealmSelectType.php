<?php

namespace App\Form;

use App\Entity\Realm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * Form for selecting a realm.
 *
 * Accepts the following options (in their legacy order):
 * * 'realms' - Realm Entities - Realms to present as option
 * * 'type' - string - Controls the type of form and translations to present.
 */
class RealmSelectType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'realm_9012356',
			'translation_domain' => 'actions',
			'realms' => null,
			'type' => 'changerealm',
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$realms = $options['realms'];
		$type = $options['type'];
		switch ($type) {
			case 'take':
				$empty	= '';
				$label	= 'control.take.realm';
				$submit	= 'control.take.submit';
				$req	= false;
				$msg      = null;
				$domain	= 'actions';
				break;
			case 'join':
				$empty	= 'diplomacy.join.empty';
				$label	= 'diplomacy.join.label';
				$submit	= 'diplomacy.join.submit';
				$req	= true;
				$msg      = 'diplomacy.join.msg';
				$domain	= 'politics';
				break;
			case 'changeoccupier':
				$empty	= '';
				$label	= 'control.changeoccupier.realm';
				$submit	= 'control.changeoccupier.submit';
				$req	= false;
				$msg      = null;
				$domain	= 'actions';
				break;
			case 'occupy':
				$empty	= '';
				$label	= 'control.occupy.realm';
				$submit	= 'control.occupy.submit';
				$req	= false;
				$msg      = null;
				$domain	= 'actions';
				break;
			default:
			case 'changerealm':
				$empty	= '';
				$label	= 'control.changerealm.realm';
				$submit	= 'control.changerealm.submit';
				$req	= false;
				$msg      = null;
				$domain	= 'actions';
				break;
		}
		$realmIDs = array();
		foreach ($realms as $each) {
			$realmIDs[] = $each->getId();
		}

		$builder->add('target', EntityType::class, array(
			'placeholder' => $empty,
			'label' => $label,
			'required' => $req,
			'class'=>Realm::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($realmIDs) {
				$qb = $er->createQueryBuilder('r');
				$qb->where('r IN (:realms)');
				$qb->setParameter('realms', $realmIDs);
				return $qb;
			},
		));
		if ($msg !== null) {
			$builder->add('message', TextareaType::class, [
				'label' => $msg,
				'translation_domain'=>'politics',
				'required' => true
			]);
		}

		$builder->add('submit', SubmitType::class, array('label'=>$submit));
	}


}
