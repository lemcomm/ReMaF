<?php

namespace App\Form;

use App\Entity\ChatMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ChatType extends AbstractType {
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'chat_14',
			'data_class'		=> ChatMessage::class,
			'translation_domain' 	=> 'dungeons',
			'csrf_protection'	=> false,
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('content', TextType::class, array(
			'label' => false,
			'required' => true,
			'attr' => array('placeholder'=>'dungeon.chat.hint'),
			'constraints' => [
				new Length([
					'max' => 500,
				]),
			],
		));

		$builder->add('submit', SubmitType::class, array('label'=>'dungeon.chat.submit'));
	}

}
