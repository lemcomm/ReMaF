<?php

namespace App\Form;

use App\Entity\DungeonMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChatType extends AbstractType {
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       	=> 'chat_14',
			'data_class'		=> DungeonMessage::class,
			'translation_domain' 	=> 'dungeons'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('content', TextType::class, array(
			'label' => false,
			'required' => true,
			'max_length' => 200,
			'attr' => array('placeholder'=>'dungeon.chat.hint')
		));

		$builder->add('submit', SubmitType::class, array('label'=>'dungeon.chat.submit'));
	}

}
