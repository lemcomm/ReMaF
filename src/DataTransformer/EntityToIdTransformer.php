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

	public function transform($entity) {
		if (null === $entity || '' === $entity) {
			return 'null';
		}
		if (!is_object($entity)) {
			throw new UnexpectedTypeException($entity, 'object');
		}
		if (!$this->unitOfWork->isInIdentityMap($entity)) {
			throw new TransformationFailedException('Entities passed to the choice field must be managed');
		}
		return $entity->getId();
	}

	public function reverseTransform($id) {
		if ('' === $id || null === $id) {
			return null;
		}
		if (!is_numeric($id)) {
			throw new UnexpectedTypeException($id, 'numeric' . $id);
		}
		$entity = $this->em->getRepository($this->class)->findOneBy(['id'=>$id]);
		if ($entity === null) {
			throw new TransformationFailedException(sprintf('The entity with key "%s" could not be found', $id));
		}
		return $entity;
	}
}
