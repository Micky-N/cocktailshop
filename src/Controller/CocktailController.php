<?php

namespace App\Controller;

use App\Entity\Cocktail;
use App\Form\CocktailType;
use App\Repository\CocktailRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CocktailController extends AbstractController
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    #[Route('/cocktails', name: 'cocktail_index')]
    public function index(Request $request, CocktailRepository $cocktailRepository, Security $security): Response
    {
        $queryBuilder = $cocktailRepository->createQueryBuilder('cocktail');
        // If user is not a VIP remove all VIP cocktail
        if (!$security->isGranted('ROLE_VIP')) {
            $queryBuilder->where('cocktail.vip = FALSE')
                // Prepare request for search
                ->andWhere('LOWER(cocktail.name) LIKE :search');
        } else {
            // Prepare request for search
            $queryBuilder->where('LOWER(cocktail.name) LIKE :search');
        }

        // Get query param search
        $search = $request->query->get('search', '');
        // Set param search
        $queryBuilder
            ->setParameter('search', '%' . strtolower($search) . '%');

        $query = $queryBuilder->getQuery();
        return $this->render('cocktail/index.html.twig', [
            'cocktails' => $query->execute(),
            'cartCocktails' => $this->cartService->getFullCart()
        ]);
    }

    #[Route('admin/cocktails', name: 'admin_cocktail_index')]
    public function indexAdmin(CocktailRepository $cocktailRepository): Response
    {
        return $this->render('admin/cocktail/index.html.twig', [
            'cocktails' => $cocktailRepository->findAll(),
            'cartCocktails' => $this->cartService->getFullCart()
        ]);
    }

    #[Route('/cocktail/{slug}', name: 'cocktail_show', priority: -1)]
    #[IsGranted('VIEW_VIP', 'cocktail', 'Cocktail not found', 404)]
    public function show(Cocktail $cocktail): Response
    {
        return $this->render('cocktail/show.html.twig', [
            'cocktail' => $cocktail,
            'cartCocktails' => $this->cartService->getFullCart(),
            'count' => $this->cartService->get()[$cocktail->getId()] ?? 0
        ]);
    }

    #[Route('admin/cocktail/new', name: 'admin_cocktail_new')]
    #[Route('admin/cocktail/{cocktail}/edit', name: 'admin_cocktail_edit')]
    #[IsGranted('EDIT', null, 'Page not found', 404)]
    public function form(Request $request, EntityManagerInterface $manager, ?Cocktail $cocktail = null): RedirectResponse|Response
    {
        // Get current cocktail in edit route otherwise new instance of Cocktail entity
        $cocktail ??= new Cocktail();
        $form = $this->createForm(CocktailType::class, $cocktail);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($cocktail);
            $manager->flush();
            return $this->redirectToRoute('admin_cocktail_index');
        }
        return $this->render('admin/cocktail/form.html.twig', [
            'form' => $form,
            'cocktail' => $cocktail,
            'cartCocktails' => $this->cartService->getFullCart(),
        ]);
    }

    #[Route('admin/cocktail/{cocktail}/delete', name: 'admin_cocktail_delete')]
    #[IsGranted('EDIT', null, 'Page not found', 404)]
    public function delete(EntityManagerInterface $manager, Cocktail $cocktail): RedirectResponse
    {
        $manager->remove($cocktail);
        $manager->flush();
        return $this->redirectToRoute('admin_cocktail_index');
    }
}
