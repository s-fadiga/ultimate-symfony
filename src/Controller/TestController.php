<?php

namespace App\Controller;

use App\Taxes\Detector;
use App\Taxes\Calculator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController {

    protected $calculator;

 

    /**
     * @Route("/homme/{age<\d+>?0}", name="test",
     * methods={"GET", "POST"})
     */
    public function homme(Request $request, $age, Calculator $calculator, Detector $detector) {

        dump($detector->detect(11));
        dump($detector->detect(101));
        $tva = $calculator->calcul(200);

        dd($tva);

        return new Response("vous avez $age ans ");
    }

    /**
     * @Route("/hello/{prenom?world}", name="hello")
     */
    public function hello(Request $request, $prenom) {
        return new Response("hello $prenom");
    }
}