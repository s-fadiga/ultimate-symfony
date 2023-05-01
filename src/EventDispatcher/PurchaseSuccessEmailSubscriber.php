<?php

namespace App\EventDispatcher;

use App\Entity\User;
use App\Event\PurchaseSuccessEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PurchaseSuccessEmailSubscriber implements EventSubscriberInterface
{
    protected $mailer;
    protected $security;
    protected $logger;
    public function __construct(MailerInterface $mailer, Security $security, LoggerInterface $logger) {
        $this->mailer = $mailer;
        $this->security = $security;
        $this->logger = $logger;
    }
    public static function getSubscribedEvents()
    {
        return [
            'purchase.success' =>'sendSuccessEmail'
        ];
    }
    public function sendSuccessEmail(PurchaseSuccessEvent $purchaseSuccessEvent) {
        
        // 1. Récuperer le user actuellement en ligne(pour connaitre son mail)
        /** @var User */
        $currentUser = $this->security->getUser();

        // 2. Récuperer la commande(dans le PurchaseSuccessEvent)
        $purchase = $purchaseSuccessEvent->getPurchase();

        // 3. Ecrire le mail(grâce aux templates)
        $email = new TemplatedEmail();
        $email->to(new Address($currentUser->getEmail(), $currentUser->getFullName()))
            ->from("admin@gmail.com")
            ->subject("Bravo votre commande n° ({$purchase->getId()}) a bien été confirmée")
            ->htmlTemplate('emails/purchase_success.html.twig')
            ->context([
                'purchase' => $purchase,
                'user' => $currentUser
            ]);

        // 4. Envoyer l'email
        $this->mailer->send($email);

        // MailerInterface
        $this->logger->info("Email envoyé pour la commande n°" . $purchaseSuccessEvent->getPurchase()->getId());

    }
}