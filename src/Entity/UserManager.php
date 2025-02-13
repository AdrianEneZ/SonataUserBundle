<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\UserBundle\Entity;

use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\UserBundle\Model\UserInterface;
use Sonata\UserBundle\Model\UserManagerInterface;

/**
 * @final since sonata-project/user-bundle 4.15
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class UserManager extends BaseUserManager implements UserManagerInterface, ManagerInterface
{
    public function findUsersBy(?array $criteria = null, ?array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, ?array $orderBy = null)
    {
        return parent::findUserBy($criteria);
    }

    public function create()
    {
        return parent::createUser();
    }

    public function save($entity, $andFlush = true): void
    {
        if (!$entity instanceof UserInterface) {
            throw new \InvalidArgumentException('Save method expected entity of type UserInterface');
        }

        parent::updateUser($entity, $andFlush);
    }

    public function delete($entity, $andFlush = true): void
    {
        if (!$entity instanceof UserInterface) {
            throw new \InvalidArgumentException('Save method expected entity of type UserInterface');
        }

        parent::deleteUser($entity);
    }

    public function getTableName()
    {
        return $this->objectManager->getClassMetadata($this->getClass())->table['name'];
    }

    public function getConnection()
    {
        return $this->objectManager->getConnection();
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/user-bundle 4.14, to be removed in 5.0.
     */
    public function getPager(array $criteria, int $page, int $limit = 10, array $sort = []): PagerInterface
    {
        $query = $this->getRepository()
            ->createQueryBuilder('u')
            ->select('u');

        $fields = $this->objectManager->getClassMetadata($this->getClass())->getFieldNames();
        foreach ($sort as $field => $direction) {
            if (!\in_array($field, $fields, true)) {
                throw new \RuntimeException(sprintf("Invalid sort field '%s' in '%s' class", $field, $this->getClass()));
            }
        }
        if (0 === \count($sort)) {
            $sort = ['username' => 'ASC'];
        }
        foreach ($sort as $field => $direction) {
            $query->orderBy(sprintf('u.%s', $field), strtoupper($direction));
        }

        if (isset($criteria['enabled'])) {
            $query->andWhere('u.enabled = :enabled');
            $query->setParameter('enabled', $criteria['enabled']);
        }

        return Pager::create($query, $limit, $page);
    }
}
