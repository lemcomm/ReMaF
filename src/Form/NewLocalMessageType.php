<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewLocalMessageType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'new_local_messsage_134',
			'translation_domain' => 'conversations',
			'settlement'	=> null,
			'place'		=> null,
		));
		$resolver->setRequired(['reply']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$place = $options['place'];
		$settlement = $options['settlement'];
		if ($options['reply']) {
			$reply = 'reply';
		} else {
			$reply = 'new';
		}

		$target = ['local'=>'conversation.target.local'];
		if ($place) {
			$target['place'] = 'conversation.target.place';
		}
		if ($settlement) {
			$target['settlement'] = 'conversation.target.settlement';
		}
		$builder->add('topic', TextType::class, array(
			'required' => false,
			'label' => 'conversation.topic.label',
			'attr' => array('size'=>40, 'maxlength'=>80)
		));
		$builder->add('type', ChoiceType::class, [
			'label' => "message.content.type",
			'multiple' => false,
			'required' => false,
			'choices' => [
				'type.letter' => 'letter',
				'type.request' => 'request',
				'type.orders' => 'orders',
				'type.report' => 'report',
				'type.rp' => 'rp',
				'type.ooc' => 'ooc'
			],
			'empty_data' => 'letter'
		]);

		$builder->add('content', TextareaType::class, array(
			'label' => 'message.content.label',
			'trim' => true,
			'required' => true
		));

		$builder->add('target', ChoiceType::class, [
			'required' => true,
			'multiple' => false,
			'expanded' => false,
			'label' => 'conversation.target.label',
			'choices' => $target,
			'placeholder' => 'conversation.target.choose',
		]);
		$builder->add('reply_to', HiddenType::class);

		$builder->add('submit', SubmitType::class, array('label'=>'message.send', 'attr'=>array('class'=>'cmsg_button')));
	}


}
