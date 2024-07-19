<?php

namespace App\Controller\Admin;

use App\Entity\Marque;
use App\Form\MarqueType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/marque")
 */
class MarqueController extends AbstractController
{
    /**
     * @Route("/", name="admin_marque_index", methods={"GET"})
     */
    public function index(EntityManagerInterface $em): Response
    {
        $marques = $em->getRepository(Marque::class)->findAll();

        return $this->render('admin/marque/index.html.twig', [
            'marques' => $marques,
        ]);
    }

    /**
     * @Route("/new", name="admin_marque_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $marque = new Marque();
        $form = $this->createForm(MarqueType::class, $marque);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($marque);
            $em->flush();

            return $this->redirectToRoute('admin_marque_index');
        }

        return $this->render('admin/marque/new.html.twig', [
            'marque' => $marque,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_marque_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Marque $marque, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MarqueType::class, $marque);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $marque->setUpdatedAt(new \DateTime());
            $em->flush();

            return $this->redirectToRoute('admin_marque_index');
        }

        return $this->render('admin/marque/edit.html.twig', [
            'marque' => $marque,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_marque_delete", methods={"POST"})
     */
    public function delete(Request $request, Marque $marque, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$marque->getId(), $request->request->get('_token'))) {
            $em->remove($marque);
            $em->flush();
        }

        return $this->redirectToRoute('admin_marque_index');
    }
}
