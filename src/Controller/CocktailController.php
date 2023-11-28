<?php

namespace App\Controller;

use App\Entity\Cocktail;
use App\Entity\Comment;
use App\Form\CocktailType;
use App\Form\CommentType;
use App\Repository\CocktailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CocktailController extends AbstractController
{

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
            'cocktails' => $query->execute()
        ]);
    }

    #[Route('admin/cocktails', name: 'admin_cocktail_index')]
    public function indexAdmin(CocktailRepository $cocktailRepository): Response
    {
        return $this->render('admin/cocktail/index.html.twig', [
            'cocktails' => $cocktailRepository->findAll()
        ]);
    }

    #[Route('/cocktail/{slug}', name: 'cocktail_show', priority: -1)]
    #[IsGranted('VIEW_VIP', 'cocktail', 'Cocktail not found', 404)]
    public function show(Cocktail $cocktail): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('comment_store', ['slug' => $cocktail->getSlug()])
        ]);

        return $this->render('cocktail/show.html.twig', [
            'cocktail' => $cocktail,
            'form' => $commentForm
        ]);
    }

    #[Route('admin/cocktail/new', name: 'admin_cocktail_new')]
    #[Route('admin/cocktail/{cocktail}/edit', name: 'admin_cocktail_edit')]
    #[IsGranted('EDIT', null, 'Page not found', 404)]
    public function form(
        Request                $request,
        EntityManagerInterface $manager,
        ?Cocktail              $cocktail = null
    ): RedirectResponse|Response
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
            'cocktail' => $cocktail
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
