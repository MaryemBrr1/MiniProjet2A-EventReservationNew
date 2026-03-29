<?php
namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservations')]
class ReservationController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_reservation_new', requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        int $id,
        EventRepository $eventRepo,
        ReservationRepository $resaRepo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $event = $eventRepo->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        // Vérifier si des places sont disponibles
        $reserved = $event->getReservations()->count();
        if ($event->getSeats() !== null && $reserved >= $event->getSeats()) {
            $this->addFlash('error', 'Désolée, cet événement est complet.');
            return $this->redirectToRoute('app_events_show', ['id' => $id]);
        }

        $reservation = new Reservation();
        $reservation->setEvent($event);
        $reservation->setUser($this->getUser());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $reservation->getEmail();

            // ✅ Vérifier si cet email a déjà réservé pour CET événement
            $dejaReserve = $resaRepo->findOneBy([
                'event' => $event,
                'email' => $email,
            ]);

            if ($dejaReserve) {
                $this->addFlash('error',
                    'L\'adresse email "' . $email . '" a déjà une réservation pour cet événement. Utilisez une autre adresse email.'
                );
                return $this->render('reservations/new.html.twig', [
                    'form' => $form->createView(),
                    'event' => $event,
                ]);
            }

            $em->persist($reservation);
            $em->flush();

            $this->addFlash('success', 'Réservation confirmée !');
            return $this->redirectToRoute('app_reservation_confirm', ['id' => $reservation->getId()]);
        }

        return $this->render('reservations/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/confirm/{id}', name: 'app_reservation_confirm', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function confirm(Reservation $reservation): Response
    {
        return $this->render('reservations/confirm.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}