<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\WorkTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @extends ServiceEntityRepository<WorkTime>
 */
class WorkTimeRepository extends ServiceEntityRepository
{
    public const HOURS_IN_DAY = 24;
    public const MINUTES_IN_HOUR = 60;
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $manager,
        private ParameterBagInterface $params,
        private ValidatorInterface $validator
    ) {
        parent::__construct($registry, WorkTime::class);
    }

    public function createWorkTimeForEmployee(array $data, Employee $employee): ?WorkTime
    {
        $startDateTime = \DateTime::createFromFormat('d.m.Y H:i', $data['start_time']);
        $endDateTime = \DateTime::createFromFormat('d.m.Y H:i', $data['end_time']);
        $workDay = \DateTime::createFromFormat('Y-m-d', $startDateTime->format('Y-m-d'));

        $maxHours = $this->params->get('work_time.max_hours_per_day');
        $interval = $startDateTime->diff($endDateTime);
        $hours = ($interval->days * self::HOURS_IN_DAY) + $interval->h + ($interval->i / self::MINUTES_IN_HOUR);

        if ($hours > $maxHours) {
            return null;
        }

        $existing = $this->findOneBy([
            'employee' => $employee,
            'workDay' => $workDay,
        ]);

        if ($existing) {
            return null;
        }

        $workTime = new WorkTime();
        $workTime
            ->setEmployee($employee)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setWorkDay($workDay);

        $errors = $this->validator->validate($workTime);
        if (count($errors) > 0) {
            return null;
        }

        $this->manager->persist($workTime);
        $this->manager->flush();

        return $workTime;
    }

    public function findBetweenDates(Employee $employee, \DateTime $start, \DateTime $end): array
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('employee', $employee))
            ->andWhere(Criteria::expr()->gte('workDay', $start))
            ->andWhere(Criteria::expr()->lte('workDay', $end));

        return $this->matching($criteria)->toArray();
    }

}
