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
        $tags = $this->getDoctrine()->getRepository(Tag::class)->getTagsForSelectPlugin();
        return new JsonResponse($tags);
    }
}
