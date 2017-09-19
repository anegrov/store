<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Provider;
use AppBundle\Form\ProviderType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Provider controller.
 *
 * @Route("/provider")
 */
class ProviderController extends Controller
{
    /**
     * Lists all News entities.
     *
     * @Route("/", name="app_provider_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:Provider')->search([]);

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Create a new Provider entity.
     *
     * @Route("/create/", name="app_provider_create")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = new Provider();
        $em->persist($entity);
        $em->flush();

        $this->addFlash('notice',
            array('status' => 'success', 'message' => 'Поставщик создан.')
        );

        return $this->redirect($this->generateUrl('app_provider_edit', ['id' => $entity->getId()]));
    }

    /**
     * Create a new Provider entity.
     *
     * @Route("/{id}/edit/", name="app_provider_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Provider')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Provider entity.');
        }

        $editForm = $this->createEditForm($entity);

        return [
            'provider' => $entity,
            'editForm' => $editForm->createView(),
        ];
    }

    /**
     * Creates a form to edit a Provider entity.
     *
     * @param Provider $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Provider $entity)
    {
        $form = $this->createForm('AppBundle\Form\ProviderType', $entity, array(
            'action' => $this->generateUrl('app_provider_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Edits an existing Provider entity.
     *
     * @Route("/{id}/", name="app_provider_update")
     * @Method("POST")
     * @Template("AppBundle:provider:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Provider')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Provider entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->addFlash('notice',
                array('status' => 'success', 'message' => 'Изменения сохранены.')
            );

            return $this->redirect($this->generateUrl('app_import_step1'));
        }

        return array(
            'entity' => $entity,
            'editForm' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Provider entity.
     *
     * @Route("/{id}/delete/", name="app_provider_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Provider')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Provider entity.');
        }

        $em->remove($entity);
        $em->flush();

        $this->addFlash('notice',
            array('status' => 'success', 'message' => 'Поставщик удален.')
        );

        return $this->redirect($this->generateUrl('app_provider_index'));
    }
}