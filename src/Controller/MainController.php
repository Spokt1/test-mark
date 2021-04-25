<?php

namespace App\Controller;

use App\Classes\Mark;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


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
        $parser = new Mark();
        return $this->json(['parsedString' => $parser->parse($str)]);
    }
}