<?php

namespace App\Form;

use App\Entity\Trade;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class TradeCancelType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'tradecancel_255',
			'trades' => [],
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$trades = $options['trades'];
		if ($trades) {
			$builder->add('trade', EntityType::class, array(
				'class'=>Trade::class, 'choice_label'=>'id', 'query_builder'=>function(EntityRepository $er) use ($trades) {
					$qb = $er->createQueryBuilder('r');
					$qb->where('r IN (:trades)');
					$qb->setParameter('trades', $trades);
					return $qb;
				},
			));
		} else {
			$builder->add('trade', ChoiceType::class, array(
					'choices' => array(0),
				)
			);
		}
	}


}
