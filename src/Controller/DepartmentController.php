<?php

namespace App\Controller;
use App\Api\ApiException;
use App\Api\ApiProblemException;
use App\Entity\Department;
use App\Form\DepartmentFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/department", name="department")
 */
class DepartmentController extends BaseController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        $departments = $this->getDoctrine()->getRepository(Department::class)->findAll();

        return $this->createApiResponse($departments);
    }

    /**
     * @Route("/{departmentId}", name="show", methods={"GET"})
     * @param $departmentId
     * @return Response
     */
    public function show($departmentId)
    {
        {
            $department = $this->getDoctrine()->getRepository(Department::class)->find($departmentId);
            if (!$department) {
                return $this->createApiException('Employee not Found!', new ApiException(404));
            }
            return $this->createApiResponse($department);
        }
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $form = $this->createForm(DepartmentFormType::class);
        try {
            $this->validateForm($form, $request);
            $doctrine = $this->getDoctrine()->getManager();
            $doctrine->persist($form->getData());
            $doctrine->flush();
            return $this->createApiResponse($form->getData(), 201);
        } catch (ApiException $exception) {
            return $this->createApiException($form->getErrors(), $exception);
        }
    }

    /**
     * @Route("/{departmentId}", name="update", methods={"PUT", "PATCH"})
     * @param Request $request
     * @param int $departmentId
     * @return Response
     */
    public function update(Request $request, int $departmentId)
    {
        $form = $this->createForm(DepartmentFormType::class);
        /** @var Department $department */
        $department = $this->getDoctrine()->getRepository(Department::class)->find($departmentId);
        if (!$department) {
            return $this->createApiException('Department not Found!', new ApiException(404));
        }
        $doctrine = $this->getDoctrine()->getManager();
        try {
            $this->validateForm($form, $request);
            /** @var Department $data */
            $data = $form->getData();
            $department->setName($data->getName());
            $doctrine->persist($department);
            $doctrine->flush();
        } catch (ApiException $exception) {
            return $this->createApiException($form->getErrors(), $exception);
        }

        return $this->createApiResponse($department, 200);
    }

    /**
     * @Route("/{departmentId}", name="delete", methods={"DELETE"})
     * @param $departmentId
     * @return Response
     */
    public function delete($departmentId)
    {
        /** @var Department $department */
        $department = $this->getDoctrine()->getRepository(Department::class)->find($departmentId);
        $doctrine = $this->getDoctrine()->getManager();
        if (!$department) {
            return $this->createApiException('Department not Found!', new ApiException(404));
        }

        $doctrine->remove($department);
        $doctrine->flush();
        return $this->createApiResponse(['message' => 'Deleted department!', 'data' => []], 200);
    }
}
