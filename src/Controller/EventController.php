<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Product;
use App\Form\EditEventType;
use App\Form\EventFilterType;
use App\Form\EventType;
use App\Form\ProductType;
use App\Repository\EventRepository;
use App\Repository\ProductRepository;
use App\Service\EmailService;
use App\Service\SeatCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventController extends AbstractController
{

    public function __construct(EmailService $emailService, SeatCalculatorService $seatCalculatorService) {
    }

    #[Route('/', name: 'home')]
    public function index(EventRepository $eventRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $isUserAuthenticated = $this->isGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(EventFilterType::class);
        $form->handleRequest($request);

        $queryBuilder = $eventRepository->createQueryBuilder('e');

        if (!$isUserAuthenticated) {
            $queryBuilder->where('e.publique = :public')
                         ->setParameter('public', true);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!empty($data['titre'])) {
                $queryBuilder->andWhere('e.titre LIKE :titre')
                             ->setParameter('titre', '%' . $data['titre'] . '%');
            }
            if (!empty($data['date'])) {
                $queryBuilder->andWhere('e.date = :date')
                             ->setParameter('date', $data['date']);
            }
        }

        $queryBuilder->orderBy('e.id', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('home.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/events', name: 'event_list')]
    #[IsGranted('ROLE_USER')]
    public function list(EventRepository $eventRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $form = $this->createForm(EventFilterType::class);
        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!empty($data['titre'])) {
                $criteria['titre'] = $data['titre'];
            }
            if (!empty($data['date'])) {
                $criteria['date'] = $data['date'];
            }
        }

        $events = $eventRepository->findByCriteria($criteria);

        return $this->render('event/list.html.twig', [
            'events' => $events,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events-creator', name: 'event_list_user')]
    #[IsGranted('ROLE_USER')]
    public function listByUser(EventRepository $eventRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(EventFilterType::class);
        $form->handleRequest($request);

        $user = $this->getUser();
        $queryBuilder = $eventRepository->createQueryBuilder('e')
            ->where('e.creator = :creator')
            ->setParameter('creator', $user);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!empty($data['titre'])) {
                $queryBuilder->andWhere('e.titre LIKE :titre')
                             ->setParameter('titre', '%' . $data['titre'] . '%');
            }
            if (!empty($data['date'])) {
                $queryBuilder->andWhere('e.date = :date')
                             ->setParameter('date', $data['date']);
            }
        }

        $queryBuilder->orderBy('e.id', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('event/listCreator.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/new', name: 'event_new')]
    public function new(Request $request, PersistenceManagerRegistry $doctrine): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user) {
                throw new AccessDeniedException('You must be logged in to register for an event.');
            } else {
                $entityManager = $doctrine->getManager();
                $event->setCreator($user);
                $entityManager->persist($event);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Event created successfully!');

            return $this->redirectToRoute('home');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/{id}/subscribe', name: 'event_subscribe')]
    public function subscribe(Event $event, EntityManagerInterface $entityManager, EmailService $emailService, SeatCalculatorService $seatCalculatorService): Response
    {
        $user = $this->getUser();
        if ($user) {
            $remainingSeats = $seatCalculatorService->calculateRemainingSeats($event);
            if ($remainingSeats > 0) {
                $event->addAttendee($user);
                $entityManager->persist($event);
                $entityManager->flush();

                $this->addFlash('success', 'Vous êtes maintenant inscrit à cet événement.');
                $emailService->sendSubscriptionConfirmationEmail($user->getEmail(), $event->getTitre());
            } else {
                $this->addFlash('danger', 'Le nombre maximum de participants a été atteint.');
            }

            return $this->redirectToRoute('event_list', [
                'id' => $event->getId()
            ]);
        } else {
            throw new AccessDeniedException('You must be logged in to subscribe to an event.');
        }
    }

    #[Route('/event/{id}/unsubscribe', name: 'event_unsubscribe')]
    public function unsubscribe(Event $event, EntityManagerInterface $entityManager, EmailService $emailService): Response
    {
        $user = $this->getUser();
        if ($user) {
            if ($event->getAttendees()->contains($user)) {
                $event->removeAttendee($user);
                $entityManager->persist($event);
                $entityManager->flush();

                $this->addFlash('success', 'Vous vous êtes désinscrit de cet événement.');
                $emailService->sendUnsubscriptionConfirmationEmail($user->getEmail(), $event->getTitre());
            }

            return $this->redirectToRoute('event_list');
        } else {
            throw new AccessDeniedException('You must be logged in to unsubscribe from an event.');
        }
    }

    #[Route('/event/{id}', name: 'event_show')]
    public function show(Event $event): Response
    {
        $this->denyAccessUnlessGranted('view_event', $event);

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/edit', name: 'event_edit')]
    #[IsGranted('edit', subject: 'event')]
    public function edit(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EditEventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/{id}/delete', name: 'event_delete', methods: ['POST'])]
    #[IsGranted('delete', subject: 'event')]
    public function delete(Event $event, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }
}