<?php

namespace App\Form;

use App\Entity\Place;
use App\Entity\Realm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Character;

class PlaceManageType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'manageplace_1947',
			'translation_domain' => 'places'
		));
		$resolver->setRequired(['description', 'me', 'char']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		/** @var Place $me */
		$me = $options['me'];
		$char = $options['char'];
		$type = $me->getType()->getName();
		$name = $me->getName();
		$formal = $me->getFormalName();
		$short = $me->getShortDescription();
		$description = $options['description'];

		$builder->add('name', TextType::class, array(
			'label'=>'names.name',
			'required'=>true,
			'data'=>$name,
			'attr' => array(
				'size'=>20,
				'maxlength'=>40,
				'title'=>'help.new.name'
			)
		));
		$builder->add('formal_name', TextType::class, array(
			'label'=>'names.formalname',
			'required'=>true,
			'data'=>$formal,
			'attr' => array(
				'size'=>40,
				'maxlength'=>160,
				'title'=>'help.new.formalname'
			)
		));

		$builder->add('short_description', TextareaType::class, array(
			'label'=>'description.short',
			'data'=>$short,
			'attr' => array('title'=>'help.new.shortdesc'),
			'required'=>true,
		));
		$builder->add('description', TextareaType::class, array(
			'label'=>'description.full',
			'attr' => array('title'=>'help.new.longdesc'),
			'data'=>$description,
			'required'=>true,
		));
		if ($type == 'embassy') {
			if ($me->isOwner($char)) {
				$builder->add('realm', EntityType::class, [
					'required'=>false,
					'choices'=> $me->getOwner()->findRealms(),
					'class'=>Realm::class,
					'choice_label' => 'name',
					'placeholder'=>'realm.empty',
					'label'=>'realm.label',
					'data'=>$me->getRealm()
				]);
			} else {
				$builder->add('realm', HiddenType::class, [
					'data'=>$me->getRealm()?->getId(),
				]);
			}
			if (!$me->getHostingRealm()) {
				$builder->add('hosting_realm', EntityType::class, [
					'required'=>false,
					'choices'=> $me->getRealm()?->findHierarchy(true),
					'class'=>Realm::class,
					'choice_label' => 'name',
					'placeholder'=>'realm.empty',
					'label'=>'hosting.label',
					'data'=>$me->getHostingRealm()
				]);
				$builder->add('owning_realm', HiddenType::class, [
					'data'=>$me->getOwningRealm()?->getId(),
				]);
				$builder->add('ambassador', HiddenType::class, [
					'data'=>$me->getAmbassador()?->getId(),
				]);
			} elseif (!$me->getOwningRealm()) {
				$builder->add('hosting_realm', EntityType::class, [
					'required'=>false,
					'choices'=> $me->getRealm()->findHierarchy(true),
					'class'=>Realm::class,
					'choice_label' => 'name',
					'data'=>$me->getHostingRealm(),
					'placeholder'=>'realm.empty',
					'label'=>'hosting.label'
				]);
				$builder->add('owning_realm', EntityType::class, [
					'required'=>false,
					'choices'=> $me->getHostingRealm()->findFriendlyRelations(),
					'class'=>Realm::class,
					'choice_label' => 'name',
					'placeholder'=>'realm.empty',
					'label'=>'owning.label',
					'data'=>$me->getOwningRealm()
				]);
				$builder->add('ambassador', HiddenType::class, [
					'data'=>null
				]);
			} else {
				$builder->add('hosting_realm', EntityType::class, [
					'required'=>false,
					'choices'=> $me->getRealm()->findHierarchy(true),
					'class'=>Realm::class,
					'choice_label' => 'name',
					'data'=>$me->getHostingRealm(),
					'placeholder'=>'realm.empty',
					'label'=>'hosting.label'
				]);
				$builder->add('owning_realm', EntityType::class, [
					'required'=>false,
					'choices'=> $me->getHostingRealm()->findFriendlyRelations(),
					'class'=>Realm::class,
					'choice_label' => 'name',
					'placeholder'=>'realm.empty',
					'label'=>'owning.label',
					'data'=>$me->getOwningRealm()
				]);
				$builder->add('ambassador', EntityType::class, [
					'required'=>false,
					'choices'=>$me->getOwningRealm()->findActiveMembers(),
					'class'=>Character::class,
					'choice_label' => 'name',
					'placeholder'=>'ambassador.empty',
					'label'=>'ambassador.label',
					'data'=>$me->getAmbassador()
				]);
			}
		} else {
			if ($me->isOwner($char)) {
				$builder->add('realm', EntityType::class, [
					'required'=>false,
					'choices'=> $char->findRealms(),
					'class'=>Realm::class,
					'choice_label' => 'name',
					'placeholder'=>'realm.empty',
					'label'=>'realm.label',
					'data'=>$me->getRealm()
				]);
			} else {
				$builder->add('realm', HiddenType::class, [
					'data'=>$me->getRealm()?->getId(),
				]);
			}
			$builder->add('hosting_realm', HiddenType::class, [
				'data'=>$me->getHostingRealm()?->getId()
			]);
			$builder->add('owning_realm', HiddenType::class, [
				'data'=>$me->getOwningRealm()?->getId()
			]);
			$builder->add('ambassador', HiddenType::class, [
				'data'=>$me->getAmbassador()?->getId()
			]);
		}
	}
}
