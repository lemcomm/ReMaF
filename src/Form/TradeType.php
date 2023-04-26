<?php

namespace App\Form;

use App\Entity\ResourceType;
use App\Entity\Settlement;
use App\Entity\Trade;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TradeType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'trade_5710',
			'data_class'	=> Trade::class,
		));
		$resolver->setRequired(['character', 'settlement', 'sources', 'dests', 'allowed']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$character = $options['character'];
		$sources = $options['sources'];
		$dests = $options['dests'];
		$builder->add('name', TextType::class, array(
			'label'=>'tradename',
			'required'=>false,
			'attr' => array('size'=>20, 'maxlength'=>40)
		));

		$builder->add('amount', IntegerType::class, array(
			'attr' => array('size'=>3)
		));

		$builder->add('resourcetype', EntityType::class, array(
			'label' => 'resource',
			'required'=>true,
			'placeholder' => 'form.choose',
			'choice_translation_domain' => true,
			'class'=>ResourceType::class,
			'choice_label'=>'name'
		));

		$builder->add('source', EntityType::class, array(
			'label' => 'source',
			'placeholder' => ($character->getOwnedSettlements()->count()>1?'form.choose':false),
			'class'=>Settlement::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($sources) {
				$qb = $er->createQueryBuilder('s');
				$qb->where('s.id in (:sources)');
				$qb->orderBy('s.name');
				$qb->setParameter('sources', $sources);
				return $qb;
			},
		));

		// you can send TO this place if it's not yours, or to any of your estates if it is
		// however, there's one additional validation that either source or target must be your current location

		// TODO: you should also be able to send to the estates of other characters who are nearby

		if ($options['allowed']) {
			$builder->add('destination', EntityType::class, array(
				'label' => 'destination',
				'placeholder' => (count($dests)>1?'form.choose':false),
				'class'=>Settlement::class,
				'choice_label'=>'name',
				'query_builder'=>function(EntityRepository $er) use ($dests) {
					$qb = $er->createQueryBuilder('s');
					$qb->where('s.id in (:dests)');
					$qb->orderBy('s.name');
					$qb->setParameter('dests', $dests);
					return $qb;
				},
			));
		} else {
			$settlement = $options['settlement'];
			$builder->add('destination', EntityType::class, array(
				'label' => 'destination',
				'placeholder' => false,
				'class'=>Settlement::class,
				'choice_label'=>'name',
				'query_builder'=>function(EntityRepository $er) use ($settlement) {
					$qb = $er->createQueryBuilder('s');
					$qb->where('s = :here');
					$qb->orderBy('s.name');
					$qb->setParameter('here', $settlement);
					return $qb;
				},
			));
		}

	}


}
