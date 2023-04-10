<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController {

    /**
     * @Route("/", name="homepage")
     */
    public function homepage(ProductRepository $productRepo,EntityManagerInterface $em) {

        $products = $productRepo->findBy([], [], 3);

        // $productRepo = $em->getRepository(Product::class);

        // $product = $productRepo->find(1);

        // $em->remove($product);
        // $em->flush();

        // dd($product);
            
        return $this->render('home.html.twig', [
            'products' => $products
        ]);
    }
}

