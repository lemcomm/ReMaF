<?php

namespace App\Form;

use App\Entity\Settlement;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * Form for rebasing a unit.
 *
 * Accepts the following options (in their legacy order):
 * * 'settlements' - array - Array of IDs for settlements a unit can rebase to.
 */
class UnitRebaseType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'rebase_12345',
			'translation_domain' => 'actions',
		));
		$resolver->setRequired(['settlements']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$all = $options['settlements'];

		$builder->add('settlement', EntityType::class, array(
			'label' => 'unit.rebase.settlement',
			'multiple'=>false,
			'expanded'=>false,
			'class'=>Settlement::class,
                        'choice_label'=>'name',
                        'query_builder'=>function(EntityRepository $er) use ($all) {
				$qb = $er->createQueryBuilder('s');
				$qb->where('s.id in (:options)')->setParameter('options', $all);
				$qb->orderBy('s.name');
				return $qb;
			},
                        'placeholder' => 'unit.rebase.none',
			'mapped'=>false,
		));

		$builder->add('submit', SubmitType::class, array(
                        'label'=>'button.submit',
                        'translation_domain'=>'settings'
		));
	}
}
