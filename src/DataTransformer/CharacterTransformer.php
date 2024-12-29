<?php

namespace App\DataTransformer;

use App\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class CharacterTransformer implements DataTransformerInterface {
	public function __construct(private EntityManagerInterface $em) {
	}

	public function transform($value): mixed {
		if (null === $value) {
			return "";
		}
		# This originally just returned the name, but we need to proof this against people with duplicate names. This returns "Name (ID: #)".
		return $value->getListName();
	}

	public function reverseTransform($value): mixed {
		if (!$value) {
			return null;
		}
		# First strip it of all non-numeric characters and see if we can find a character.
		$id = preg_replace('/[^1234567890]*/', '', $value);
		$character = false;
		if ($id) {
			$character = $this->em->getRepository(Character::class)->findOneBy(['id'=>$id, 'alive' => TRUE]);
			if ($character) {
				echo $character->getName();
			}
		} else {
			# Presumably, that wasn't an ID. Assume it's just a name. Strip out parantheses and numbers.
			$name = trim(preg_replace('/[123456790()]*/', '', $value));
			$potentials = $this->em->getRepository(Character::class)->findBy(array('name' => $name, 'alive' => TRUE), array('id' => 'ASC'));
			foreach ($potentials as $each) {
				if ($each->getRetired()) {
					continue;
				} else {
					$character = $each;
					break;
				}
			}
			if (!$character) {
				$name = preg_replace('/(<\/i>)+/', '', preg_replace('/(<i>)+/', '', $name));
				$character = $this->em->getRepository(Character::class)->findOneBy(['known_as' => $name, 'alive' => TRUE], ['id' => 'ASC']);
			}
		}

		if (!$character) {
			# There's a few ways this could happen. No matching name, malformed input (someone messing with the preformatted ones), or no matching ID.
			return null;
		}
		/*
		if (null === $character) {
			throw new TransformationFailedException(sprintf(
				'Character named "%s" does not exist!',
				$name
			));
		}
		*/

		return $character;
	}
}
