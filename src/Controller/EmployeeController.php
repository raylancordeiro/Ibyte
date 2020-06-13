<?php

namespace App\Controller;

use App\Api\ApiException;
use App\Api\ApiProblemException;
use App\Entity\Employee;
use App\Form\EmployeeFormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/employee", name="employee")
 */
class EmployeeController extends BaseController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        $employees = $this->getDoctrine()->getRepository(Employee::class)->findAll();

        return $this->createApiResponse($employees);
    }

    /**
     * @Route("/{employeeId}", name="show", methods={"GET"})
     * @param $employeeId
     * @return Response
     */
    public function show($employeeId)
    {
        {
            $employee = $this->getDoctrine()->getRepository(Employee::class)->find($employeeId);
            if (!$employee) {
                return $this->createApiException('Employee not Found!', new ApiException(404));
            }
            return $this->createApiResponse($employee);
        }
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $form = $this->createForm(EmployeeFormType::class);
        try {
            $this->validateForm($form, $request);
            $doctrine = $this->getDoctrine()->getManager();
            $doctrine->persist($form->getData());
            $doctrine->flush();
            return $this->createApiResponse($form->getData(), 200);
        } catch (ApiException $exception) {
            return $this->createApiException('Invalid Form', $exception);
        }
    }

    /**
     * @Route("/{employeeId}", name="update", methods={"PUT", "PATCH"})
     * @param Request $request
     * @param int $employeeId
     * @return Response
     */
    public function update(Request $request, int $employeeId)
    {
        $form = $this->createForm(EmployeeFormType::class);
        /** @var Employee $employee */
        $employee = $this->getDoctrine()->getRepository(Employee::class)->find($employeeId);
        if (!$employee) {
            return $this->createApiException('Employee not Found!', new ApiException(404));
        }
        $doctrine = $this->getDoctrine()->getManager();
        try {
            $this->validateForm($form, $request);
            /** @var Employee $data */
            $data = $form->getData();
            $employee->setUpdateData($data);
            $doctrine->persist($employee);
            $doctrine->flush();
        } catch (ApiException $exception) {
            return $this->createApiException($form->getErrors(), $exception);
        }

        return $this->createApiResponse($employee, 200);
    }

    /**
     * @Route("/{employeeId}", name="delete", methods={"DELETE"})
     * @param $employeeId
     */
    public function delete($employeeId)
    {
        /** @var Employee $employee */
        $employee = $this->getDoctrine()->getRepository(Employee::class)->find($employeeId);
        $doctrine = $this->getDoctrine()->getManager();
        if (!$employee) {
            return $this->createApiException('Employee not Found!', new ApiException(404));
        }

        $doctrine->remove($employee);
        $doctrine->flush();
        return $this->createApiResponse(['message' => 'Deleted employee!', 'data' => $employee], 200);
    }
}

