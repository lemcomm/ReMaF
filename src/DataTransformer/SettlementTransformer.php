<?php

namespace App\DataTransformer;

use App\Entity\Settlement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SettlementTransformer implements DataTransformerInterface {
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

		$settlement = $this->em->getRepository(Settlement::class)->findOneByName($value);

		if (null === $settlement) {
			throw new TransformationFailedException(sprintf(
				'Settlement named "%s" does not exist!',
				$value
			));
		}
		return $settlement;
	}
}