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
	public function transform($value): mixed {
		if (null === $value) {
			return "";
		}

		return $value->getName();
	}

	public function reverseTransform($value): mixed {
		if (!$value) {
			return null;
		}

		$realm = $this->em->getRepository(Realm::class)->findOneBy(['name'=>$value]);

		if (null === $realm) {
			throw new TransformationFailedException(sprintf(
				'Realm named "%s" does not exist!',
				$value
			));
		}
		return $realm;
	}
}