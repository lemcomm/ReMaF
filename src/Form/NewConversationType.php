<?php

namespace App\Form;

use App\Entity\Character;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class NewConversationType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'new_conversation_134',
			'translation_domain' => 'conversations',
			'contacts'	=> [],
			'distance'	=> 0,
			'settlement'	=> null,
			'realm'		=> null,
		));
		$resolver->setRequired(['char']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('topic', TextType::class, array(
			'required' => true,
			'label' => 'conversation.topic.label',
			'attr' => array('size'=>40, 'maxlength'=>80)
		));
		$builder->add('type', ChoiceType::class, [
			'label' => "message.content.type",
			'multiple' => false,
			'required' => false,
			'choices' => [
				'letter' => 'type.letter',
				'request' => 'type.request',
				'orders' => 'type.orders',
				'report' => 'type.report',
				'rp' => 'type.rp',
				'ooc' => 'type.ooc'
			],
			'empty_data' => 'letter'
		]);

		$builder->add('content', TextareaType::class, array(
			'label' => 'message.content.label',
			'trim' => true,
			'required' => true
		));

		if (!$options['realm']) {

			$me = $options['char'];
			$maxdistance = $options['distance'];

			if ($me->getPrisonerOf()) {
				$captor = $me->getPrisonerOf();
				$builder->add('captor', EntityType::class, array(
					'required' => false,
					'multiple'=>true,
					'expanded'=>true,
					'label'=>'conversation.captor.label',
					'class'=>Character::class, 'property'=>'name', 'query_builder'=>function(EntityRepository $er) use ($captor) {
						$qb = $er->createQueryBuilder('c');
						$qb->where('c.alive = true');
						$qb->andWhere('c = :captor')->setParameter('captor', $captor);
						return $qb;
				}));
			}

			$builder->add('nearby', EntityType::class, array(
				'required' => false,
				'multiple'=>true,
				'expanded'=>true,
				'label'=>'conversation.nearby.label',
				'class'=>Character::class, 'property'=>'name', 'query_builder'=>function(EntityRepository $er) use ($me, $maxdistance) {
					$qb = $er->createQueryBuilder('c');
					$qb->from('App\Entity\Character', 'me');
					$qb->where('c.alive = true');
					$qb->andWhere('me = :me')->andWhere('c != me')->setParameter('me', $me);
					if ($maxdistance) {
						$qb->andWhere('ST_Distance(me.location, c.location) < :maxdistance')->setParameter('maxdistance', $maxdistance);
					}
					if ($inside = $me->getInsideSettlement()) {
						$qb->andWhere('c.inside_settlement = :inside')->setParameter('inside', $inside);
					} else {
						$qb->andWhere('c.inside_settlement IS NULL');
					}
					$qb->orderBy('c.name', 'ASC');
					return $qb;
			}));

			$settlement = $options['settlement'];
			if ($settlement && $settlement->getOwner() && $settlement->getOwner() != $me) {
				$owner = $settlement->getOwner();
				$builder->add('owner', EntityType::class, array(
					'required' => false,
					'multiple'=>true,
					'expanded'=>true,
					'label'=>'conversation.owner.label',
					'class'=>Character::class, 'property'=>'name', 'query_builder'=>function(EntityRepository $er) use ($owner) {
						$qb = $er->createQueryBuilder('c');
						$qb->where('c.alive = true');
						$qb->andWhere('c = :owner')->setParameter('owner', $owner);
						return $qb;
				}));
			}

			$contacts = $options['contacts'];

			if ($contacts) {
				$builder->add('contacts', EntityType::class, array(
					'required' => false,
					'multiple'=>true,
					'expanded'=>true,
					'label' => 'conversation.recipients.label',
					'placeholder' => 'conversation.recipients.empty',
					'class' => Character::class,
					'property' => 'name',
					'query_builder'=>function(EntityRepository $er) use ($contacts) {
						$qb = $er->createQueryBuilder('c');
						$qb->where('c IN (:recipients)');
						$qb->orderBy('c.name', 'ASC');
						$qb->setParameter('recipients', $contacts);
						return $qb;
					},
				));
			}
		}

		$builder->add('submit', SubmitType::class, array('label'=>'conversation.create', 'attr'=>array('class'=>'cmsg_button')));
	}


}
