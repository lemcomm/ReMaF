<?php

namespace App\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class CharacterTransformer implements DataTransformerInterface {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}

	public function transform($character) {
		if (null === $character) {
			return "";
		}
		# This originally just returned the name, but we need to proof this against people with duplicate names. This returns "Name (ID: #)".
		return $character->getListName();
	}

	public function reverseTransform($input) {
		if (!$input) {
			return null;
		}
		# First strip it of all non-numeric characters and see if we can find a character.
		$id = preg_replace('/[^1234567890]*/', '', $input);
		if ($id) {
			$character = $this->em->getRepository('BM2SiteBundle:Character')->findOneBy(['id'=>$id, 'alive' => TRUE]);
			if ($character) {
				echo $character->getName();
			}
		} else {
			# Presumably, that wasn't an ID. Assume it's just a name. Strip out parantheses and numbers.
			$name = trim(preg_replace('/[123456790()]*/', '', $input));
			$potentials = $this->em->getRepository('BM2SiteBundle:Character')->findBy(array('name' => $name, 'alive' => TRUE), array('id' => 'ASC'));
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
				$character = $this->em->getRepository('BM2SiteBundle:Character')->findOneBy(['known_as' => $name, 'alive' => TRUE], ['id' => 'ASC']);
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
