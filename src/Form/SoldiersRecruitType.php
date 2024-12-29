<?php

namespace App\Form;

use App\Entity\EquipmentType;
use App\Entity\Unit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Form for recruiting new soldiers.
 *
 * Accepts the following options (in their legacy order):
 * * 'available_equipment' - EquipmentType Entities - Equipment that can be issued to recruits.
 * * 'units' - Unit Entities - Units that can accept recruits.
 */
class SoldiersRecruitType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'	=> 'recruit_23469',
			'attr'		=> array('class'=>'wide'),
			'validation_constraint' => new Assert\Collection(array(
				'number' => new Assert\Range(array('min'=>1)),
				'weapon' => null,
				'armour' => null,
				'equipment' => null,
				'mount' => null
		        ))
		));
		$resolver->setRequired(['units','available_equipment']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$available = $options['available_equipment'];
		$units = $options['units'];
		$equipment = array();
		foreach ($available as $a) {
			$equipment[] = $a['item']->getId();
		}

		$builder->add('unit', EntityType::class, array(
			'label' => 'recruit.troops.unit',
			'required' => true,
			'class' => Unit::class,
			'choice_label' => 'name',
			'choices' => $units,
			'translation_domain'=>'actions'
		));

		$builder->add('number', IntegerType::class, array(
			'attr' => array('size'=>3)
		));

		$fields = array('weapon', 'armour', 'equipment', 'mount');
		foreach ($fields as $field) {
			$builder->add($field, EntityType::class, array(
				'label'=>$field,
				'placeholder'=>'item.none',
				'required'=> ($field==='weapon'),
				'choice_label'=>'nameTrans',
				'class'=>EquipmentType::class,
				'choice_translation_domain' => true,
				'query_builder'=>function(EntityRepository $er) use ($equipment, $field) {
					return $er->createQueryBuilder('e')->where('e in (:available)')->andWhere('e.type = :type')->orderBy('e.name')
						->setParameters(array('available'=>$equipment, 'type'=>$field));
			}));
		}

		$builder->add('submit', SubmitType::class, array(
			'label'=>'recruit.troops.submit',
			'translation_domain'=>'actions'
		));
	}


}
