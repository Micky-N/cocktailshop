<?php

namespace App\Controller;

use App\Entity\Cocktail;
use App\Entity\User;
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
    public function index(CocktailRepository $cocktailRepository, Security $security): Response
    {
        $builder = $cocktailRepository->createQueryBuilder('c');
        if (!$security->isGranted('ROLE_VIP')) {
            $builder->where('c.vip = FALSE');
        }
        $query = $builder->getQuery();
        return $this->render('cocktail/index.html.twig', [
            'cocktails' => $query->execute(),
            'cartCocktails' => $this->cartService->getFullCart()
        ]);
    }

    #[Route('admin/cocktails', name: 'admin_cocktail_index')]
    public function indexAdmin(CocktailRepository $cocktailRepository, Security $security): Response
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
    public function delete(EntityManagerInterface $manager, ?Cocktail $cocktail = null): RedirectResponse
    {
        if (!$cocktail) return $this->redirectToRoute('admin_cocktail_index');
        $manager->remove($cocktail);
        $manager->flush();
        return $this->redirectToRoute('admin_cocktail_index');
    }
}
