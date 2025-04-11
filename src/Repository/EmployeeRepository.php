<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @extends ServiceEntityRepository<Employee>
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private ValidatorInterface $validator, private EntityManagerInterface $manager)
    {
        parent::__construct($registry, Employee::class);
    }

    public function createEmployee(array $data): ?Employee
    {
        $employee = new Employee();
        $employee
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setPesel($data['pesel']);

        $errors = $this->validator->validate($employee);

        if (count($errors) > 0) {
            return null;
        }

        $manager = $this->manager;
        $manager->persist($employee);

        $manager->flush();

        return $employee;
    }

    public function findByPesel(string $pesel): ?Employee
    {
        return $this->findOneBy(['pesel' => $pesel]);
    }
}
