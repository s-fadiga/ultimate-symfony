<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Cart\CartService;
use App\Entity\PurchaseItem;
use App\Form\CartConfirmationType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PurchaseConfirmationController extends AbstractController {

    protected $em;
    protected $cartService;
    public function __construct(EntityManagerInterface $em, CartService $cartService){
        $this->em = $em;
        $this->cartService = $cartService;
    }

    /**
     * @Route("purchase/confirm", name="purchase_confirm")
     * @IsGranted("ROLE_USER", message="Vous devez être connecter pour confirmer cette commande")
     */
    public function confirm(Request $request) {

        // 1 ere étape
        // lire les données du formulaire
        $form = $this->createForm(CartConfirmationType::class);

        $form->handleRequest($request);

        // si le formulaire n'est pas soumis : message d'erreur
        if (!$form->isSubmitted()) {
            $this->addFlash('warning', 'Vous devez remplir le formulaire pour passer la commande');

            return $this->redirectToRoute('cart_show');
        }

        // 2eme étape
        // si on est pas connecté : message d'erreur avec IsGranted
        $user = $this->getUser();

        // 3eme étape
        // s'il n'y a pas de produits dans le panier alors message d'erreur
        $cartItems = $this->cartService->getDetailCartItem();
        if (count($cartItems) === 0) {
            $this->addFlash('warning', 'Vous ne pouvez confirmer une commande avec un panier vide');

            return $this->redirectToRoute('cart_show');
        }

        // 4eme étape si les 3 premiers tests sont bon
        // Creer une purchase (commande)
        /**
         * @var Purchase
         */
        $purchase = $form->getData();

        // 5eme étape: lier la commande à l'utilisateur connecté
        $purchase->setUser($user)
                ->setPurchasedAt(new DateTime())
                ->setTotal($this->cartService->getTotal());

        $this->em->persist($purchase);

        // 6eme étape: lier la commande aux produits qui sont dans le panier
        foreach ($this->cartService->getDetailCartItem() as $cartItem) {
            $purchaseItem = new PurchaseItem;

            $purchaseItem->setPurchase($purchase)
                        ->setProduct($cartItem->product)
                        ->setProductName($cartItem->product->getName())
                        ->setQuantity($cartItem->qty)
                        ->setTotal($cartItem->getTotal())
                        ->setProductPrice($cartItem->product->getPrice());

            $this->em->persist($purchaseItem);
        }

        // 7eme étape enfin enregistrer la commande
        $this->em->flush();
        
        return $this->redirectToRoute('purchase_payment_form', [
            'id' => $purchase->getId()
        ]);
    }
}