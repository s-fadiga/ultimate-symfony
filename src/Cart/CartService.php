<?php

namespace App\Cart;

use App\Cart\CartItem;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {

    protected $session;
    protected $productRepository;
    public function __construct(SessionInterface $session, ProductRepository $productRepository) {
        
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    // incrementer un produit
    public function add(int $id) {
         // Retrouver le panier s'il existe sinon prendre 1 array vide
         $cart = $this->session->get('cart', []);

         // si le produit n'existe pas dans le panier, panier=0 
         if (!array_key_exists($id, $cart)) {
            $cart[$id] = 0;
         }
         // sinon on ajoute +1
         $cart[$id]++;
         
         // mise a jour du panier
         $this->session->set('cart', $cart);
    }

    // decrementer un produit
    public function decrement(int $id) {

        $cart = $this->session->get('cart', []);

        // si il n'y a pas de produit on retourne panier=vide
        if (!array_key_exists($id, $cart)) {
            return;
        }

        // soit le produit est = 1 on le supprime
        if ($cart[$id] === 1) {
            $this->remove($id);
            return;
        }
        // soit le produit est > 1 on le décrémente
        $cart[$id]--;
        
        $this->session->set('cart', $cart);
    }

    // supprimer un produit du panier
    public function remove(int $id) {

        $cart = $this->session->get('cart', []);

        unset($cart[$id]);

        $this->session->set('cart', $cart);
    }


    // calcul du total du panier
    public function getTotal(): int
    {
        $total = 0;

        foreach ($this->session->get('cart', []) as $id => $qty) {
            $product = $this->productRepository->find($id);

            if (!$product) {
                continue;
            }

            $total += $product->getPrice() * $qty;
        }
        return $total;
    }
    
    public function getDetailCartItem(): array
    {
        $detailCart = [];

        foreach ($this->session->get('cart', []) as $id => $qty) {
            $product = $this->productRepository->find($id);

            if (!$product) {
                continue;
            }

            $detailCart[] = new CartItem($product, $qty);
        }
        return $detailCart;
    }
}