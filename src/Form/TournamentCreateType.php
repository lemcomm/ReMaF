<?php

namespace App\Form;

use App\Entity\Character;
use App\Entity\EquipmentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TournamentCreateType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'activitySelect_12331',
			'translation_domain' 	=> 'activity',
		));
		$resolver->setRequired(['categories', 'skills']);
	}
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('name', TextType::class, array(
			'label'=>'tournament.form.name',
			'required'=>false
		));

		$builder->add('submit', SubmitType::class, [
			'label'=>'tournament.form.submit'
		]);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function duelFields(FormBuilderInterface $builder, array $options): void {

	}

}
