<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;


class MessageReplyType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'message_reply_9234',
			'translation_domain' => 'conversations',
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
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

		$builder->add('conversation', HiddenType::class);
		$builder->add('reply_to', HiddenType::class);
		$builder->add('preview', SubmitType::class, array('label'=>'message.preview', 'attr'=>array('class'=>'cmsg_button')));
		$builder->add('submit', SubmitType::class, array('label'=>'message.reply.submit', 'attr'=>array('class'=>'cmsg_button')));
	}

}
