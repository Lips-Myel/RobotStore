<?php

namespace App\Controller;

use App\Entity\Robot;
use App\Form\RobotType;
use App\Repository\RobotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/robot')]
final class RobotController extends AbstractController
{
    #[Route(name: 'app_robot_index', methods: ['GET'])]
    public function index(RobotRepository $robotRepository): Response
    {
        return $this->render('robot/index.html.twig', [
            'robots' => $robotRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_robot_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $robot = new Robot();
        $form = $this->createForm(RobotType::class, $robot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($robot);
            $entityManager->flush();

            return $this->redirectToRoute('app_robot_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('robot/new.html.twig', [
            'robot' => $robot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_robot_show', methods: ['GET'])]
    public function show(Robot $robot): Response
    {
        return $this->render('robot/show.html.twig', [
            'robot' => $robot,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_robot_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Robot $robot, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RobotType::class, $robot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_robot_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('robot/edit.html.twig', [
            'robot' => $robot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_robot_delete', methods: ['POST'])]
    public function delete(Request $request, Robot $robot, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$robot->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($robot);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_robot_index', [], Response::HTTP_SEE_OTHER);
    }
}
