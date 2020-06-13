<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmployeeRepository", repositoryClass=EmployeeRepository::class)
 */
class Employee
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank
     */
    private $career;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="employees", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCareer(): ?string
    {
        return $this->career;
    }

    public function setCareer(string $career): self
    {
        $this->career = $career;

        return $this;
    }

    /**
     * @return int|Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     *
     * @return Employee
     */
    public function setDepartment(Department $department)
    {
        $this->department = $department;

        return $this;
    }

    public function setUpdateData(Employee $employee): Employee
    {
        $this->firstName = $employee->getFirstName();
        $this->lastName = $employee->getLastName();
        $this->career = $employee->getCareer();
        $this->department = $employee->getDepartment();

        return $this;
    }
}
