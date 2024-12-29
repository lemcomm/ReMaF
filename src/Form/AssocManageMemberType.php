<?php

namespace App\Form;

use App\Entity\AssociationRank;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssocManageMemberType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'updatembr_1779',
			'translation_domain' => 'orgs'
		));
		$resolver->setRequired(['ranks', 'me']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$ranks = $options['ranks'];
		$me = $options['me'];
		$builder->add('rank', EntityType::class, array(
			'label'=>'assoc.form.member.rank',
			'required'=>true,
			'placeholder' => 'assoc.form.select',
			'class' => AssociationRank::class,
			'choice_translation_domain' => true,
			'choice_label' => 'name',
			'choices' => $ranks,
			'data' => $me?$me->getRank():null
		));
		$builder->add('submit', SubmitType::class, array('label'=>'assoc.form.submit'));
	}
}
