<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Form\CartConfirmationType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Summary of CartController
 */
class CartController extends AbstractController
{
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CartService
     */
    protected $cartService;

    public function __construct(ProductRepository $productRepository, CartService $cartService){
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    /**
     * @Route("/cart", name="cart_show")
     */
    public function show(CartService $cartService) {

        $form = $this->createForm(CartConfirmationType::class);

        $detailcart = $cartService->getDetailCartItem();

        $total = $cartService->getTotal();
     
        return $this->render('cart/index.html.twig', [
            'items' => $detailcart,
            'total' => $total,
            'confirmationForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add", requirements={"id": "\d+"})
     */
    public function add($id, Request $request)
    {
        // est-ce que le produit existe
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas !");
        }

        $this->cartService->add($id);

        $this->addFlash('success', "Le produit a bien été ajouté au panier");

        if ($request->query->get('returnToCart')) {

            return $this->redirectToRoute("cart_show");
        }
        
        return $this->redirectToRoute('product_show', [
            'show_one_product' => $product->getCategory()->getSlug(),
            'slug' => $product->getSlug()
        ]);
    }

    /**
     * @Route("/cart/decrement/{id}", name="cart_decrement", requirements={"id": "\d+"})
     */
    public function decrement($id){

        // est-ce que le produit existe
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas et ne pas être enlevé !");
        }

        $this->cartService->decrement($id);

        $this->addFlash('success', "Vous avez retiré un produit du panier");

        return $this->redirectToRoute("cart_show");
    }

    /**
     * @Route("/cart/delete/{id}", name="cart_delete", requirements={"id": "\d+"})
     */
    public function delete($id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas et ne pas être supprimé");
        }

        $this->cartService->remove($id);

        $this->addFlash("success",  "Le produit a bien été supprimé du panier");

        return $this->redirectToRoute("cart_show");
    }

}
