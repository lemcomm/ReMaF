<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class PlacePermissionsSetType extends AbstractType {
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'pps_499912',
			'data_class'		=> 'App\Entity\Place',
			'translation_domain' => 'places'
		));
		$resolver->setRequired(['me', 'owner', 'p']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$owner = $options['owner'];
		$me = $options['me'];
		$p = $options['p'];
		if ($owner) {
			if ($p->getType()->getPublic() === false) {
				$builder->add('public', CheckboxType::class, [
					'required'=> false,
					'label'=> 'control.place.public',
				]);
			}

			$builder->add('permissions', CollectionType::class, array(
				'entry_type'		=> PlacePermissionsType::class,
				'entry_options' => [
					'me' => $me,
					'p' => $p,
				],
				'allow_add'	=> true,
				'allow_delete' => true,
				'constraints' => new Valid(),
			));
		} else {
			$builder->add('permissions', CollectionType::class, array(
				'entry_type'		=> PlaceOccupationPermissionsType::class,
				'entry_options' => [
					'me' => $me,
					'p' => $p,
				],
				'allow_add'	=> true,
				'allow_delete' => true,
				'constraints' => new Valid(),
			));
		}
	}
}
