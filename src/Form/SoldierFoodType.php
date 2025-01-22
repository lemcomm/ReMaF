<?php

namespace App\Form;

use App\Entity\Settlement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

class SoldierFoodType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'	=> 'soldierfood_1998',
			'translation_domain' => 'actions',
			'settlements'	=> [],
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$settlements = $options['settlements'];

		$builder->add('subject', TextType::class, array(
			'label' => 'request.generic.subject',
			'required' => true,
			'attr' => array('title'=>'request.generic.help.subject')
		));
		$builder->add('text', TextareaType::class, array(
			'label' => 'request.generic.text',
			'required' => true,
			'attr' => array('title'=>'request.generic.help.text')
		));
		$builder->add('target', EntityType::class, array(
			'label' => 'request.soldierfood.estate',
			'class'=>Settlement::class,
			'choice_label'=>'name',
			'choices'=>$settlements,
			'query_builder'=>function(EntityRepository $er) use ($settlements) {
				$qb = $er->createQueryBuilder('s');
				$qb->where('s in :settlements')->setParameter('settlements', $settlements)->orderBy('s.realm.name', 'ASC')->addOrderBy('s.name');
				return $qb;
				},
			#'query_builder'=>function(EntityRepository $er) use ($settlements, $char) {
			#	$qb = $er->createQueryBuilder('s');
			#	$qb->join('s.realm', 'r')->where('s.realm IN (:realms)')->andWhere('s.owner != :char')->setParameters(array('realms'=>$realms, 'char'=>$char));
			#	$qb->orderBy('r.name')->addOrderBy('s.name');
			#	return $qb;
			#},
			'group_by' => function($val, $key, $index) {
				if ($val->getRealm()) {
					return $val->getRealm()->getName();
				} else {
					return '--';
				}
			},
			'attr' => array('title'=>'request.soldierfood.estatehelp'),
			'mapped'=>false,
		));
		$builder->add('limit', NumberType::class, array(
			'label' => 'request.soldierfood.limit',
			'attr' => array('title'=>'request.soldierfood.limithelp'),
			'required' => false
		));


		$builder->add('expires', DateTimeType::class, array(
			'attr' => array('title'=>'request.generic.help.expires'),
			'required' => false,
			'placeholder' => array('year' => 'request.generic.year', 'month'=> 'request.generic.month', 'day'=>'request.generic.day', 'hour'=>'request.generic.hour', 'minute'=>'request.generic.minute'),
			'years' => array(date("Y"), strval((int) (date("Y"))+1), strval((int) (date("Y"))+2))
		));

		$builder->add('submit', SubmitType::class, array('label'=>'request.generic.submit'));
	}
}
