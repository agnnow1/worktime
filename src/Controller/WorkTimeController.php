<?php

namespace App\Controller;

use App\Repository\EmployeeRepository;
use App\Repository\WorkTimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WorkTimeController extends AbstractController
{
    #[Route('/api/work-time', name: 'register_work_time', methods: ['POST'])]
    public function registerWorkTime(Request $request, WorkTimeRepository $workTimeRepository, EmployeeRepository $employeeRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $employee = $employeeRepository->find($data['employee_id']);

        if (!$employee) {
            return new JsonResponse(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $workTime = $workTimeRepository->createWorkTimeForEmployee($data, $employee);
        if (!$workTime) {
            return new JsonResponse(['error' => 'Failed to register work time. It might exceed the allowed hours or already exist for the given day.'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'Work time created'], Response::HTTP_CREATED);
    }
}