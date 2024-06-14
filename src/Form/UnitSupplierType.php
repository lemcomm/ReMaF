<?php

namespace App\Form;

use App\Entity\Settlement;
use App\Entity\Unit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for configuring unit settings.
 *
 * Accepts the following options (in their legacy order):
 * * 'supply' - boolean - Determines if supply cane be changed.
 * * 'settlements' - array - Settlements that can be used for supply
 * * 'settings' - UnitSettings Entity (null) - UnitSettings entity to edit
 * * 'lord' - boolean - Determines if lord only options appear
 */
class UnitSupplierType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'unitsupply_1337',
			'translation_domain' 	=> 'settings',
			'attr'			=> array('class'=>'wide'),
			'data_class'		=> Unit::class,
		));
		$resolver->setRequired(['settlements']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		#TODO: Stuff.
		$settlements = $options['settlements'];
		$builder->add('supplier', EntityType::class, array(
			'label' => 'unit.supplier.name',
			'multiple'=>false,
			'expanded'=>false,
			'class'=>Settlement::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($settlements) {
				$qb = $er->createQueryBuilder('s');
				$qb->where('s IN (:settlements)')
					->setParameters(array('settlements'=>$settlements));
				$qb->orderBy('s.name');
				return $qb;
			},
			'placeholder' => 'unit.supplier.empty',
		));

		$builder->add('submit', SubmitType::class, array('label'=>'button.submit'));
	}
}
