<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use App\Stripe\StripeService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchasePaymentController extends AbstractController
{

    /**
     * @Route("/purchase/pay/{id}", name="purchase_payment_form")
     * @IsGranted("ROLE_USER", message="Vous devez être connecter pour acceder à cette page")
     */
    public function showCardForm($id, PurchaseRepository $purchaseRepository, StripeService $stripeService) {

        $purchase = $purchaseRepository->find($id);

        if (!$purchase || 
                ($purchase && $purchase->getUser() !== $this->getUser()) ||
                ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID)) {
        }
        
        $paymentIntent = $stripeService->getPaymentIntent($purchase);
    
        return $this->render('purchase/payment.html.twig', [
            'clientSecret' => $paymentIntent->client_secret,
            'purchase' => $purchase,
            'stripePublicKey' =>$stripeService->getPublicKey()
        ]);
    }
}