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

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'new_local_messsage_134',
			'translation_domain' => 'conversations',
			'settlement'	=> null,
			'place'		=> null,
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$place = $options['place'];
		$settlement = $options['settlement'];

		$target = ['conversation.target.local'=>'local'];
		if ($place) {
			$target['conversation.target.place'] = 'place';
		}
		if ($settlement) {
			$target['conversation.target.settlement'] = 'settlement';
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

		$builder->add('preview', SubmitType::class, array('label'=>'message.preview', 'attr'=>array('class'=>'cmsg_button')));
		$builder->add('submit', SubmitType::class, array('label'=>'message.send', 'attr'=>array('class'=>'cmsg_button')));
	}


}
