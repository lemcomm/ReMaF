<?php

namespace App\Form;

use BM2\SiteBundle\Form\SettlementOccupationPermissionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;

use BM2\SiteBundle\Entity\Character;

/**
 * Form for updating settlement owner permissions.
 *
 * Accepts the following options (in their legacy order):
 * * 'settlement' - Settlement Entity - Settlement for which you are editing permissions.
 * * 'me' - Character Entity - Character doing the editing.
 */
class SettlementPermissionsSetType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'sps_41312',
			'data_class'		=> 'BM2\SiteBundle\Entity\Settlement',
			'translation_domain' => 'actions'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$lord = $options['lord'];
		$me = $options['me'];
		if ($lord) {
			$builder->add('allow_thralls', CheckboxType::class, array(
				'label' => "control.permissions.thralls",
				'required' => false,
			));

			$builder->add('feed_soldiers', CheckboxType::class, array(
				'label' => "control.permissions.feedsoldiers",
				'required' => false,
			));

			$builder->add('permissions', CollectionType::class, array(
				'type'		=> SettlementPermissionsType::class,
				'entry_options'	=> [
					'me'=>$me,
					'settlement'=>$builder->getData(),
				],
				'allow_add'	=> true,
				'allow_delete' => true,
				'cascade_validation' => true
			));
		} else {
			$builder->add('occupation_permissions', CollectionType::class, array(
				'type'		=> SettlementOccupationPermissionsType::class,
				'entry_options'	=> [
					'me'=>$me,
					'settlement'=>$builder->getData(),
				],
				'allow_add'	=> true,
				'allow_delete' => true,
				'cascade_validation' => true
			));
		}
	}
}
