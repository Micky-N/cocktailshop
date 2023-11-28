<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user->getProfile()->getBillingAddress()){
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('order/index.html.twig', [
            'orders' => $user->getProfile()->getOrders(),
        ]);
    }

    #[Route('/order/{order}/invoice', name: 'order_invoice')]
    public function generatePdf(Order $order): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user->getProfile()->getBillingAddress()){
            return $this->redirectToRoute('app_profile');
        }
        $html = $this->renderView('order/invoice.html.twig', [
            'order' => $order,
            'billingAddress' => $user->getProfile()->getBillingAddress()
        ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // Définissez les options du papier si nécessaire
        $dompdf->setPaper('A4');

        // Rendu du PDF
        $dompdf->render();

        // Retournez une réponse Symfony avec le PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            ['content-type' => 'application/pdf']
        );
    }
}
