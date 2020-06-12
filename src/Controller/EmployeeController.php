<?php

namespace App\Controller;

use App\Entity\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/employee", name="employee")
 */
class EmployeeController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        $employees = $this->getDoctrine()->getRepository(Employee::class)->findAll();

        return $this->json([
            'data' => $employees
        ]);
    }

    /**
     * @Route("/{employeeId}", name="show", methods={"GET"})
     * @param $employeeId
     */
    public function show($employeeId)
    {
        //TODO: Implementar
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     * @param Request $request
     */
    public function create(Request $request)
    {
        //TODO: Implementar
    }

    /**
     * @Route("/{employeeId}", name="update", methods={"PUT", "PATCH"})
     * @param $employeeId
     */
    public function update($employeeId)
    {
        //TODO: Implementar
    }

    /**
     * @Route("/{employeeId}", name="delete", methods={"DELETE"})
     * @param $employeeId
     */
    public function delete($employeeId)
    {
        //TODO: Implementar
    }
}
