<?php

namespace App\Form;

use App\Entity\Listing;
use App\Entity\Permission;
use App\Entity\Settlement;
use App\Entity\SettlementPermission;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * Form for updating settlement owner permissions.
 *
 * Accepts the following options (in their legacy order):
 * * 'settlement' - Settlement Entity - Settlement for which you are editing permissions.
 * * 'me' - Character Entity - Character doing the editing.
 */
class SettlementPermissionsType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'settlementpermissions_68956351',
			'translation_domain' => 'politics',
			'data_class'		=> SettlementPermission::class,
		));
		$resolver->setRequired(['settlement', 'me']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$s = $options['settlement'];
		$me = $options['me'];
		$builder->add('settlement', EntityType::class, array(
			'required' => true,
			'class'=>Settlement::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($s) {
				return $er->createQueryBuilder('s')->where('s = :s')->setParameter('s',$s);
			}
		));
		// TODO: filter according to what's available? (e.g. no permission for docks at regions with no coast)
		$builder->add('permission', EntityType::class, array(
			'required' => true,
			'choice_translation_domain' => true,
			'class'=>Permission::class,
			'choice_label'=>'translation_string',
			'query_builder'=>function(EntityRepository $er) {
				return $er->createQueryBuilder('p')->where('p.class = :class')->setParameter('class', 'settlement');
			}
		));
		$builder->add('value', IntegerType::class, array(
			'required' => false,
		));
		$builder->add('reserve', IntegerType::class, array(
			'required' => false,
		));

		$builder->add('listing', EntityType::class, array(
			'required' => true,
			'placeholder'=>'perm.choose',
			'class'=>Listing::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($me) {
				return $er->createQueryBuilder('l')->where('l.owner = :me')->setParameter('me',$me->getUser());
			}
		));
	}
}
