<?php
/**
 * Created by PhpStorm.
 * User: giorgiopagnoni
 * Date: 16/05/17
 * Time: 17:53
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getTagsForSelectPlugin()
    {
        return $this->createQueryBuilder('t')
            ->select('t.id, t.name AS text')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}