<?php

namespace App\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use App\Entity\Realm;

class RealmTransformer implements DataTransformerInterface {
	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}
	public function transform($realm) {
		if (null === $realm) {
			return "";
		}

		return $realm->getName();
	}

	public function reverseTransform($name) {
		if (!$name) {
			return null;
		}

		$realm = $this->em->getRepository(Realm::class)->findOneBy(['name'=>$name]);

		if (null === $realm) {
			throw new TransformationFailedException(sprintf(
				'Realm named "%s" does not exist!',
				$name
			));
		}
		return $realm;
	}
}