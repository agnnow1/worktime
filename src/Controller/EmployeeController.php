<?php

namespace App\Controller;

use App\Repository\EmployeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

final class EmployeeController extends AbstractController
{
    #[Route('api/employee', name: 'create_employee', methods: ['POST'])]
    public function createEmployee(Request $request, EmployeeRepository $employeeRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $existingEmployee = $employeeRepository->findOneBy(['pesel' => $data['pesel']]);

        if ($existingEmployee) {
            return new JsonResponse([
                'error' => 'An employee with this PESEL already exists.'
            ], Response::HTTP_CONFLICT);
        }

        $employee = $employeeRepository->createEmployee($data);

        if (!$employee) {
            return new JsonResponse([
                'message' => 'Invalid data or validation errors.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'id' => $employee->getId()
        ], 201);

    }
}
