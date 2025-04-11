<?php

namespace App\Controller;

use Symfony\Component\Uid\Uuid;
use App\Repository\EmployeeRepository;
use App\Repository\WorkTimeRepository;
use App\Service\WorkTimeService;
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

    #[Route('/api/work-time/summary/day', name: 'daily_summary_work_time', methods: ['GET'])]
    public function getDailySummary(Request $request, WorkTimeService $workTimeService): JsonResponse
    {
        $employeeId = $request->query->get('employee_id');
        $workDay = $request->query->get('work_day');

        if (!$employeeId || !$workDay || !Uuid::isValid($employeeId)) {
            return new JsonResponse(['error' => 'Missing employeeId or date or invalid uuid'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $summary = $workTimeService->calculateEmployeeDailySummary($employeeId, $workDay);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['response' => $summary]);
    }

    #[Route('/api/work-time/summary/month', name: 'monthly_summary_work_time', methods: ['GET'])]
    public function getMonthSummary(Request $request, WorkTimeService $workTimeService): JsonResponse
    {
        $employeeId = $request->query->get('employee_id');
        $monthYear = $request->query->get('date');

        if (!$employeeId || !$monthYear || !Uuid::isValid($employeeId)) {
            return new JsonResponse(['error' => 'Missing employeeId or date or invalid uuid'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $summary = $workTimeService->calculateEmployeeMonthlySummary($employeeId, $monthYear);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['response' => $summary]);
    }
}
