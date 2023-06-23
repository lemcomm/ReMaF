<?php

namespace App\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Settlement;

class SettlementTransformer implements DataTransformerInterface {
	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}

	public function transform($settlement) {
		if (null === $settlement) {
			return "";
		}

		return $settlement->getName();
	}

	public function reverseTransform($name) {
		if (!$name) {
			return null;
		}

		$settlement = $this->em->getRepository('BM2SiteBundle:Settlement')->findOneByName($name);

		if (null === $settlement) {
			throw new TransformationFailedException(sprintf(
				'Settlement named "%s" does not exist!',
				$name
			));
		}
		return $settlement;
	}
}