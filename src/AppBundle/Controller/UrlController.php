<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class UrlController extends Controller
{
    /**
     * @Route("/", name="main_page")
     */
    public function indexAction(Request $request)
    {
        return $this->render('url/create/form.html.twig');
    }

    /**
     * @Route("/shorten", name="shorten_url")
     * @Method("POST")
     */
    public function shortenPostAction(Request $request)
    {
        if ($request->getContentType() == 'json') {
            $jsonData = json_decode($request->getContent(), true);
            $request->request->replace($jsonData);
        }
        $basicUrl = $request->get('basic_url');
        $desiredShortUrl = $request->get('url_alias');

        $entityManager = $this->getDoctrine()->getManager();
        $urlEntity = $entityManager->getRepository('AppBundle:Url')
                     ->shorten($basicUrl, $desiredShortUrl);

        $errors = $this->get('validator')->validate($urlEntity);
        if (count($errors) == 0) {
            $entityManager->persist($urlEntity);
            $entityManager->flush();
            $responseData = ['shorted_url' => $urlEntity->getUrlAlias()];
        } else {
            $errorsMessages = [];
            foreach ($errors as $error) {
                $errorsMessages[] = $error->getMessage();
            }
            $responseData = ['errors' => $errorsMessages];
        }
        return new JsonResponse($responseData);
    }

    /**
     * @Route("/{urlAlias}", name="redirect_by_alias")
     */
    public function redirectAliasAction($urlAlias)
    {
        $urlRepository = $this->getDoctrine()->getRepository('AppBundle:Url');
        $urlEntity = $urlRepository->findOneByUrlAlias($urlAlias);
        if (empty($urlEntity)) {
            throw $this->createNotFoundException();
        }

        $urlEntity->increaseUsagesCount();
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($urlEntity);
        $manager->flush();

        $redirectUrl = $urlEntity->getBasicUrl();
        return $this->redirect($redirectUrl);
    }

    /**
     * @Route("/statistics/{urlAlias}", name="url_statistics")
     */
    public function statisticsAction($urlAlias)
    {
        $urlEntity = $this->getDoctrine()->getRepository('AppBundle:Url')->findOneByUrlAlias($urlAlias);
        if (empty($urlEntity)) {
            throw new $this->createNotFoundException();
        }
        $renderData = [
            'url_alias' => $urlEntity->getUrlAlias(),
            'basic_url' => $urlEntity->getBasicUrl(),
            'usages_count' => $urlEntity->getUsagesCount()
        ];
        return $this->render('url/statistics.html.twig', $renderData);
    }
}
