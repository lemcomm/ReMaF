<?php

namespace App\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\TransformationFailedException;


class EntityToIdTransformer implements DataTransformerInterface {

	protected EntityManagerInterface $em;
	protected mixed $class;
	protected UnitOfWork $unitOfWork;

	public function __construct(EntityManagerInterface $em, $class) {
		$this->em = $em;
		$this->unitOfWork = $this->em->getUnitOfWork();
		$this->class = $class;
	}

	public function transform($value): mixed {
		if (null === $value || '' === $value) {
			return 'null';
		}
		if (!is_object($value)) {
			throw new UnexpectedTypeException($value, 'object');
		}
		if (!$this->unitOfWork->isInIdentityMap($value)) {
			throw new TransformationFailedException('Entities passed to the choice field must be managed');
		}
		return $value->getId();
	}

	public function reverseTransform($value): mixed {
		if ('' === $value || null === $value) {
			return null;
		}
		if (!is_numeric($value)) {
			throw new UnexpectedTypeException($value, 'numeric' . $value);
		}
		$entity = $this->em->getRepository($this->class)->findOneBy(['id'=>$value]);
		if ($entity === null) {
			throw new TransformationFailedException(sprintf('The entity with key "%s" could not be found', $value));
		}
		return $entity;
	}
}
