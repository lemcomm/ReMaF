<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use App\Entity\User;
use App\Entity\Culture;


class CultureType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'culture_9413',
			'attr'		=> array('class'=>'wide'),
			'user'		=> User::class,
			'available'	=> true,
			'old_culture'	=> null,
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$user = $options['user'];
		$old_culture = $options['old_culture'];
		if ($options['available']) {
			$builder->add('culture', EntityType::class, array(
				'label' => 'settlement.namepack',
				'required' => true,
				'choice_translation_domain' => true,
				'class'=>Culture::class,
				'query_builder'=>function(EntityRepository $er) use ($user, $old_culture) {
					$qb = $er->createQueryBuilder('c');
					$qb->leftJoin('c.users', 'u')
						->where('u = :me')->setParameter('me', $user)
						->orWhere('c.free = true');
					if ($old_culture) {
						$qb->orWhere('c = :old')->setParameter('old', $old_culture);
					}
					return $qb;
				},
			));
			$builder->add('submit', SubmitType::class, array('label'=>'account.culture.change'));
		} else {
			$builder->add('culture', EntityType::class, array(
				'label' => 'settlement.namepack',
				'multiple' => true,
				'expanded' => true,
				'required' => true,
				'choice_translation_domain' => true,
				'class'=>Culture::class,
				'query_builder'=>function(EntityRepository $er) use ($user) {
					$qb = $er->createQueryBuilder('c');
					$qb->where('c.free = false');
					$owned = array();
					foreach ($user->getCultures() as $culture) {
						$owned[]=$culture->getId();
					}
					if (!empty($owned)) {
						$qb->andWhere('c NOT IN (:owned)')->setParameter('owned',$owned);
					}
					return $qb;
				},
			));
			$builder->add('submit', SubmitType::class, array('label'=>'account.culture.submit'));
		}

	}

}
