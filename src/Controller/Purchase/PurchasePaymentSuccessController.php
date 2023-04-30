<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Cart\CartService;
use App\Event\PurchaseSuccessEvent;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PurchasePaymentSuccessController extends AbstractController
{
    /**
     * @Route("/purchase/terminate/{id}", name="purchase_payment_success")
     * @IsGranted("ROLE_USER", message="Vous devez être connecter pour acceder à cette ressource")
     */
    public function success($id, PurchaseRepository $purchaseRepository, EntityManagerInterface $em, CartService $cartService, EventDispatcherInterface $dispatcher){

        //1. Je récupére la commande
        $purchase = $purchaseRepository->find($id);

        if (!$purchase || 
                ($purchase && $purchase->getUser() !== $this->getUser()) ||
                ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID)) {
            
            $this->addFlash('warning', "Cette commande n'existe pas");
            return $this->redirectToRoute("purchase_index");
        }
        //2. Je la fait passer au status payé
        $purchase->setStatus((Purchase::STATUS_PAID));
        $em->flush();

        //3. je vide le panier
        $cartService->empty();

        // Lancer un evenement qui permet aux autres developpeurs de réagir à la prise d'une commande
        $purchaseEvent = new PurchaseSuccessEvent($purchase);
        $dispatcher->dispatch($purchaseEvent, 'purchase.success');

        //4. je redirige avec un flash vers la liste des commandes
        $this->addFlash('success', "La commande a été payée et confirmée");
        return $this->redirectToRoute("purchase_index");
    }
}