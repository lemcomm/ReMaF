<?php

namespace App\Form;

use App\Entity\Settlement;
use App\Entity\War;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


/**
 * Form for starting a war.
 *
 * Accepts the following options (in their legacy order):
 * * 'me' - Character - Character starting the war.
 */
class WarType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'war_1911',
			'translation_domain' 	=> 'actions',
			'data_class'		=> War::class,
			'attr'			=> array('class'=>'wide')
		));
		$resolver->setRequired(['me']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('summary', TextType::class, array(
			'label'=>'military.war.summary',
			'required'=>true,
			'attr' => array('size'=>80, 'maxlength'=>240)
		));
		$builder->add('description', TextareaType::class, array(
			'label'=>'military.war.desc',
			'required'=>true,
		));
		$me = $options['me'];
		$builder->add('targets', EntityType::class, array(
			'label'=>'military.war.targets',
			'required'=>true,
			'multiple'=>true,
			'mapped'=>false,
			'placeholder'=>'form.choose',
			'class'=>Settlement::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($me) {
				return $er->createQueryBuilder('s')->where('s.realm NOT IN (:me)')->orderBy('s.name')->setParameters(array('me'=>$me));
			}
		));

		$builder->add('submit', SubmitType::class, array('label'=>'military.war.declare'));
	}
}
