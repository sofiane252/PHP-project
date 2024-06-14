<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\EventRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    // #[Route('/', name: 'home')]
    // public function index(EventRepository $eventRepository, PaginatorInterface $paginator, Request $request): Response
    // {
    //     $isUserAuthenticated = $this->isGranted('IS_AUTHENTICATED_FULLY');

    //     $queryBuilder = $eventRepository->createQueryBuilder('e');

    //     if (!$isUserAuthenticated) {
    //         $queryBuilder->where('e.publique = :public')
    //                      ->setParameter('public', true);
    //     }

    //     $queryBuilder->orderBy('e.id', 'ASC');

    //     $pagination = $paginator->paginate(
    //         $queryBuilder,
    //         $request->query->getInt('page', 1),
    //         9
    //     );

    //     return $this->render('home.html.twig', [
    //         'pagination' => $pagination,
    //     ]);
    // }
}
