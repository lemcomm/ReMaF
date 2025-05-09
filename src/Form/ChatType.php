<?php

namespace App\Form;

use App\Entity\ChatMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChatType extends AbstractType {
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       	=> 'chat_14',
			'data_class'		=> ChatMessage::class,
			'translation_domain' 	=> 'dungeons',
			'csrf_protection'	=> false,
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		#TODO: Rework this to be a TextareaType that resizes on pages with enter to submit and shift+enter for new line.
		$builder->add('content', TextType::class, array(
			'label' => false,
			'required' => true,
			'attr' => array('placeholder'=>'dungeon.chat.hint'),
			'constraints' => [
				new Length([
					'max' => 500,
				]),
				new NotBlank([
				])
			],
		));

		$builder->add('submit', SubmitType::class, array('label'=>'dungeon.chat.submit'));
	}

}
