<?php

namespace App\Service;

use App\Repository\EmployeeRepository;
use App\Repository\WorkTimeRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WorkTimeService
{
    public function __construct(
        private readonly WorkTimeRepository $workTimeRepository,
        private readonly EmployeeRepository $employeeRepository,
        private readonly ParameterBagInterface $params
    ) {}

    public function calculateEmployeeDailySummary(string $employeeId, string $dateString): array
    {
        $employee = $this->employeeRepository->find($employeeId);

        if (!$employee) {
            throw new \InvalidArgumentException('Employee not found.');
        }

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception) {
            throw new \InvalidArgumentException('Invalid date format. Use YYYY-MM-DD.');
        }

        $workTimes = $this->workTimeRepository->findBy([
            'employee' => $employee,
            'workDay' => $date,
        ]);

        $totalMinutes = 0;
        foreach ($workTimes as $workTime) {
            $interval = $workTime->getStartDateTime()->diff($workTime->getEndDateTime());
            $minutes = ($interval->days * WorkTimeRepository::HOURS_IN_DAY * WorkTimeRepository::MINUTES_IN_HOUR) + ($interval->h * WorkTimeRepository::MINUTES_IN_HOUR) + $interval->i;
            $totalMinutes += $minutes;
        }

        $hoursDecimal = $totalMinutes / WorkTimeRepository::MINUTES_IN_HOUR;
        $roundedHours = round($hoursDecimal * 2) / 2;

        $rate = $this->params->get('work.base_rate');
        $sum = $roundedHours * $rate;

        return [
            'total_after_calculation' => $sum . ' PLN',
            'hours_for_the_given_day' => $roundedHours,
            'hourly_rate' => $rate . ' PLN',
        ];
    }

    public function calculateEmployeeMonthlySummary(string $employeeId, string $monthYear): array {
        $employee = $this->employeeRepository->find($employeeId);

        if (!$employee) {
            throw new \InvalidArgumentException('Employee not found.');
        }

        $start = $this->parseMonthYear($monthYear);
        $end = (clone $start)->modify('last day of this month');

        $workTimes = $this->workTimeRepository->findBetweenDates($employee, $start, $end);

        $totalMinutes = 0;
        foreach ($workTimes as $wt) {
            $interval = $wt->getStartDateTime()->diff($wt->getEndDateTime());
            $minutes = ($interval->days * WorkTimeRepository::HOURS_IN_DAY * WorkTimeRepository::MINUTES_IN_HOUR) + ($interval->h * WorkTimeRepository::MINUTES_IN_HOUR) + $interval->i;
            $totalMinutes += $minutes;
        }

        $baseRate = (float) $this->params->get('work.base_rate');
        $overtimeMultiplier = 2.0;
        $norm = (float) $this->params->get('work.norm_hours');

        $decimalHours = $totalMinutes / WorkTimeRepository::MINUTES_IN_HOUR;
        $rounded = round($decimalHours * 2) / 2;

        $standard = min($rounded, $norm);
        $overtime = max(0, $rounded - $norm);

        $total = ($standard * $baseRate) + ($overtime * $baseRate * $overtimeMultiplier);

        return [
            'normal_hours_in_the_given_month' => $standard,
            'hourly_rate' => $baseRate . ' PLN',
            'overtime_hours_in_the_given_month' => $overtime,
            'overtime_rate' => ($baseRate * $overtimeMultiplier) . ' PLN',
            'total_after_calculation' => round($total, 2) . ' PLN'
        ];
    }

    private function parseMonthYear(string $input): \DateTime
    {
        $parts = explode('.', $input);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Date format must be MM.YYYY');
        }

        [$month, $year] = $parts;
        if (!checkdate((int)$month, 1, (int)$year)) {
            throw new \InvalidArgumentException('Invalid date.');
        }

        return new \DateTime("$year-$month-01");
    }
}