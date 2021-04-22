<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;


class MainController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     */
    public function main()
    {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("/parseText", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function parseText(Request $request): JsonResponse
    {
        $str = $request->get('markDownInput');

        $environment = new Environment();
        $environment->addExtension(new InlinesOnlyExtension());
        $converter = new CommonMarkConverter([], $environment);

        return $this->json(['parsedString' => $converter->convertToHtml($str)]);
    }
}