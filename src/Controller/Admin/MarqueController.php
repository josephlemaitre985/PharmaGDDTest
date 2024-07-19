<?php

namespace App\Controller\Admin;

use App\Entity\Marque;
use App\Form\MarqueType;
use App\Service\MarqueWorkflowService;
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
    private $workflowService;

    public function __construct(MarqueWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

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
            $marque->setCreatedAt(new \DateTime());
            $marque->setUpdatedAt(new \DateTime());

            // Initialiser l'état à "draft"
            $this->workflowService->revertToDraft($marque);

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

            // Mettre à jour l'état à "draft" si ce n'est pas déjà "published"
            if ($this->workflowService->getCurrentPlace($marque) !== 'published') {
                $this->workflowService->revertToDraft($marque);
            }

            $em->flush();

            return $this->redirectToRoute('admin_marque_index');
        }

        return $this->render('admin/marque/edit.html.twig', [
            'marque' => $marque,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/publish", name="admin_marque_publish", methods={"POST"})
     */
    public function publish(Request $request, Marque $marque, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('publish'.$marque->getId(), $request->request->get('_token'))) {
            $this->workflowService->publish($marque);
            $em->flush();
        }

        return $this->redirectToRoute('admin_marque_index');
    }

    /**
     * @Route("/{id}/require-changes", name="admin_marque_require_changes", methods={"POST"})
     */
    public function requireChanges(Request $request, Marque $marque, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('require_changes'.$marque->getId(), $request->request->get('_token'))) {
            $this->workflowService->requireChanges($marque);
            $em->flush();
        }

        return $this->redirectToRoute('admin_marque_index');
    }

    /**
     * @Route("/{id}/revert-to-draft", name="admin_marque_revert_to_draft", methods={"POST"})
     */
    public function revertToDraft(Request $request, Marque $marque, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('revert_to_draft'.$marque->getId(), $request->request->get('_token'))) {
            $this->workflowService->revertToDraft($marque);
            $em->flush();
        }

        return $this->redirectToRoute('admin_marque_index');
    }

    /**
     * @Route("/{id}/delete", name="admin_marque_delete", methods={"POST"})
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
