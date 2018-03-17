<?php

namespace Ferus\FCFSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Event
 */
class Event
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var integer
     * @Assert\NotBlank()
     */
    private $maxPeople;

    /**
     * @var \Datetime
     * @Assert\NotBlank
     */
    private $date;

    /**
     * @var \Datetime
     * @Assert\NotBlank
     */
    private $registrationDate;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $registrations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registrations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get maxPeople
     *
     * @return integer
     */
    public function getMaxPeople()
    {
        return $this->maxPeople;
    }

    /**
     * Set maxPeople
     *
     * @param integer $maxPeople
     * @return Event
     */
    public function setMaxPeople($maxPeople)
    {
        $this->maxPeople = $maxPeople;

        return $this;
    }

    /**
     * @param \Datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \Datetime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \Datetime $registrationDate
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    }

    /**
     * @return \Datetime
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * Add registrations
     *
     * @param \Ferus\FCFSBundle\Entity\EventRegistration $registrations
     * @return Event
     */
    public function addRegistration(\Ferus\FCFSBundle\Entity\EventRegistration $registrations)
    {
        $this->registrations[] = $registrations;

        return $this;
    }

    /**
     * Remove registrations
     *
     * @param \Ferus\FCFSBundle\Entity\EventRegistration $registrations
     */
    public function removeRegistration(\Ferus\FCFSBundle\Entity\EventRegistration $registrations)
    {
        $this->registrations->removeElement($registrations);
    }

    /**
     * Get registrations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRegistrations()
    {
        return $this->registrations;
    }
}
