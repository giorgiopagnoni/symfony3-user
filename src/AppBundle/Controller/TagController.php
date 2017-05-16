<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/tags")
 */
class TagController extends Controller
{
    /**
     * @Route("/", name="tag_list")
     * @Security("has_role('ROLE_USER')")
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        /** @var EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Tag::class);
        $query = $repository->createQueryBuilder('t')
            ->select('t.id, t.name AS text')
            ->orderBy('t.name')
            ->getQuery();
        $tags = $query->getResult();

        return new JsonResponse($tags);
    }
}
