<?php

namespace App\Form;

use App\Entity\Character;
use App\Entity\EquipmentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class EntourageManageType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'entouragemanage_1456',
			'translation_domain' => 'actions',
			'others'	=> []
		));
		$resolver->setRequired(['entourage']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('npcs', FormType::class);

		foreach ($options['entourage'] as $npc) {
			$idstring = (string)$npc->getId();
			$builder->get('npcs')->add($idstring, FormType::class, array('label'=>$npc->getName()));
			$field = $builder->get('npcs')->get($idstring);

			if (!$npc->isLocked()) {
				if ($npc->getAlive()) {
					$actions = array('recruit.manage.disband' => 'disband2');
					if (!empty($this->others)) {
						$actions['recruit.manage.assign'] = 'assign2';
					}
					if ($npc->getCharacter() && $npc->getCharacter()->isNPC()) {
						unset($actions['assign2']); // bandits cannot assign entourage
					}
				} else {
					$actions = array('recruit.manage.bury' => 'bury');
				}
				$field->add('action', ChoiceType::class, array(
					'choices' => $actions,
					'required' => false,
					'choice_translation_domain' => true,
					'attr' => array('class'=>'action')
				));
				if ($npc->getType()->getName()=="follower") {
					$field->add('supply', EntityType::class, array(
						'placeholder' => 'food',
						'required' => false,
						'class'=>EquipmentType::class,
						'choice_label'=>'nameTrans',
						'query_builder'=>function(EntityRepository $er) {
							return $er->createQueryBuilder('e')->orderBy('e.name', 'ASC');
						},
						'data' => $npc->getEquipment(),
						'choice_translation_domain' => 'messages',
						'translation_domain' => 'messages'
					));
				}
			}
		}

		if (!empty($options['others'])) {
			$others = $options['others'];
			$builder->add('assignto', EntityType::class, array(
				'placeholder' => 'form.choose',
				'label' => 'recruit.manage.assignto2',
				'required' => false,
				'class'=>Character::class, 'choice_label'=>'name', 'query_builder'=>function(EntityRepository $er) use ($others) {
					$qb = $er->createQueryBuilder('c');
					$qb->where('c IN (:others)');
					$qb->setParameter('others', $others);
					return $qb;
				},
			));
		}

	}

}
