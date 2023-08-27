<?php

namespace App\Form;

use App\Entity\AssociationRank;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;

class AssocCreateRankType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'newassocrank_13349',
			'translation_domain' => 'orgs'
		));
		$resolver->setRequired(['ranks', 'me']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$ranks = $options['ranks'];
		$me = $options['me'];
		$builder->add('name', TextType::class, array(
			'label'=>'assoc.form.createRank.name',
			'required'=>true,
			'attr' => array(
				'size'=>20,
				'maxlength'=>40,
				'title'=>'assoc.help.rankname'
			),
			'data' => $me ? $me->getName() : null
		));
		$builder->add('description', TextareaType::class, array(
			'label'=>'assoc.form.description.full',
			'attr' => array('title'=>'assoc.help.rankdesc'),
			'data' => ($me && $me->getDescription()) ? $me->getDescription()->getText() : null
		));
		$builder->add('viewAll', CheckboxType::class, array(
			'label'=>'assoc.form.createRank.viewAll',
			'required'=>false,
			'attr' => array('title'=>'assoc.help.viewAll'),
			'data' => $me ? $me->getViewAll() : null
		));
		$builder->add('viewUp', IntegerType::class, array(
			'label'=>'assoc.form.createRank.viewUp',
			'required'=>false,
			'attr' => array('title'=>'assoc.help.viewUp'),
			'constraints' => [
				new GreaterThan([
					'value' => -1,
				]),
			],
			'data' => ($me && $me->getViewUp() !== null) ? $me->getViewUp() : null
		));
		$builder->add('viewDown', IntegerType::class, array(
			'label'=>'assoc.form.createRank.viewDown',
			'required'=>false,
			'attr' => array('title'=>'assoc.help.viewDown'),
			'constraints' => [
				new GreaterThan([
					'value' => -1,
				]),
			],
			'data' => ($me && $me->getViewDown() !== null) ? $me->getViewDown() : null
		));
		$builder->add('viewSelf', CheckboxType::class, array(
			'label'=>'assoc.form.createRank.viewSelf',
			'required'=>false,
			'attr' => array('title'=>'assoc.help.public'),
			'data' => ($me && $me->getViewSelf() !== null) ? $me->getViewSelf() : null
		));
		$builder->add('superior', EntityType::class, array(
			'label'=>'assoc.form.createRank.superior',
			'required'=>false,
			'placeholder' => 'assoc.form.createRank.selectsuperior',
			'attr' => array('title'=>'assoc.help.superior'),
			'class' => AssociationRank::class,
			'choice_translation_domain' => true,
			'choice_label' => 'name',
			'choices' => $ranks,
			'data' => $me? $me->getSuperior() : null
		));
		$builder->add('build', CheckboxType::class, array(
			'label'=>'assoc.form.createRank.build',
			'required'=>false,
			'attr' => array('title'=>'assoc.help.build'),
			'data' => ($me && $me->getBuild() !== null) ? $me->getBuild() : null
		));
		$builder->add('createSubs', CheckboxType::class, array(
			'label'=>'assoc.form.createRank.createSubs',
			'required'=>false,
			'attr' => array('title'=>'assoc.help.createSubs'),
			'data' => ($me && $me->getSubCreate() !== null) ? $me->getSubCreate() : null
		));
		$builder->add('manager', CheckboxType::class, array(
			'label'=>'assoc.form.createRank.manager',
			'required'=>false,
			'attr' => array('title'=>'assoc.help.manager'),
			'data' => ($me && $me->getManager() !== null) ? $me->getManager() : null
		));
		$builder->add('createAssocs', CheckboxType::class, array(
			'label'=>'assoc.form.createRank.createAssocs',
			'required'=>false,
			'attr' => array('title'=>'assoc.help.createAssocs'),
			'data' => ($me && $me->getCreateAssocs() !== null) ? $me->getCreateAssocs() : null
		));
		$builder->add('submit', SubmitType::class, array('label'=>'assoc.form.submit'));
	}
}
