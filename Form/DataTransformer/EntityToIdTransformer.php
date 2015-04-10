<?php

namespace ITE\FormBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Class EntityToIdTransformer
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    private $em;
    private $class;
    private $property;
    private $queryBuilder;
    private $multiple;

    private $unitOfWork;

    public function __construct(EntityManager $em, $class, $property, $queryBuilder, $multiple)
    {
        if (!(null === $queryBuilder || $queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder or \Closure');
        }

        if (null == $class) {
            throw new UnexpectedTypeException($class, 'string');
        }

        $this->em = $em;
        $this->unitOfWork = $this->em->getUnitOfWork();
        $this->class = $class;
        $this->queryBuilder = $queryBuilder;
        $this->multiple = $multiple;

        if ($property) {
            $this->property = $property;
        }
    }

    public function transform($data)
    {
        if (null === $data) {
            return null;
        }

        if (!$this->multiple) {
            return $this->transformSingleEntity($data);
        }

        $return = array();

        foreach ($data as $element) {
            $return[] = $this->transformSingleEntity($element);
        }

        return implode(', ', $return);
    }

    protected function splitData($data)
    {
        return explode(',', $data);
    }


    protected function transformSingleEntity($data)
    {
        if (!$this->unitOfWork->isInIdentityMap($data)) {
            throw new FormException('Entities passed to the choice field must be managed');
        }

        if ($this->property) {
            $propertyPath = new PropertyPath($this->property);
            return $propertyPath->getValue($data);
        }

        return current($this->unitOfWork->getEntityIdentifier($data));
    }

    public function reverseTransform($data)
    {
        if (!$data) {
            return null;
        }

        if (!$this->multiple) {
            return $this->reverseTransformSingleEntity($data);
        }

        $return = array();

        foreach ($this->splitData($data) as $element) {
            $return[] = $this->reverseTransformSingleEntity($element);
        }

        return $return;
    }

    protected function reverseTransformSingleEntity($data)
    {
        $em = $this->em;
        $class = $this->class;
        $repository = $em->getRepository($class);

        if ($qb = $this->queryBuilder) {
            if ($qb instanceof \Closure) {
                $qb = $qb($repository, $data);
            }

            try {
                $result = $qb->getQuery()->getSingleResult();
            } catch (NoResultException $e) {
                $result = null;
            }
        } else {
            if ($this->property) {
                $result = $repository->findOneBy(array($this->property => $data));
            } else {
                $result = $repository->find($data);
            }
        }

        if (!$result) {
            throw new TransformationFailedException('Can not find entity');
        }

        return $result;
    }
}