<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BusinessTripWorkLog implements SpecialWorkLogInterface
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    /**
     * @var string
     */
    private $purpose;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $transport;

    /**
     * @var string
     */
    private $expectedDeparture;

    /**
     * @var string
     */
    private $expectedArrival;

    /**
     * @var \DateTimeImmutable|null
     */
    private $timeApproved;

    /**
     * @var \DateTimeImmutable|null
     */
    private $timeRejected;

    /**
     * @var string|null
     */
    private $rejectionMessage;

    /**
     * @var BusinessTripWorkLogSupport[]
     */
    private $support;

    /**
     * @var WorkMonth
     */
    private $workMonth;

    public function __construct()
    {
        $this->date = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
        $this->purpose = '';
        $this->destination = '';
        $this->transport = '';
        $this->expectedDeparture = '';
        $this->expectedArrival = '';
        $this->support = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): BusinessTripWorkLog
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): BusinessTripWorkLog
    {
        $this->date = $date;

        return $this;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): BusinessTripWorkLog
    {
        $this->purpose = $purpose;

        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): BusinessTripWorkLog
    {
        $this->destination = $destination;

        return $this;
    }

    public function getTransport(): string
    {
        return $this->transport;
    }

    public function setTransport(string $transport): BusinessTripWorkLog
    {
        $this->transport = $transport;

        return $this;
    }

    public function getExpectedDeparture(): string
    {
        return $this->expectedDeparture;
    }

    public function setExpectedDeparture(string $expectedDeparture): BusinessTripWorkLog
    {
        $this->expectedDeparture = $expectedDeparture;

        return $this;
    }

    public function getExpectedArrival(): string
    {
        return $this->expectedArrival;
    }

    public function setExpectedArrival(string $expectedArrival): BusinessTripWorkLog
    {
        $this->expectedArrival = $expectedArrival;

        return $this;
    }

    public function getTimeApproved(): ?\DateTimeImmutable
    {
        return $this->timeApproved;
    }

    public function setTimeApproved(?\DateTimeImmutable $timeApproved): BusinessTripWorkLog
    {
        $this->timeApproved = $timeApproved;

        return $this;
    }

    public function markApproved(): BusinessTripWorkLog
    {
        $this->timeApproved = new \DateTimeImmutable();

        return $this;
    }

    public function markRejected(string $rejectionMessage): BusinessTripWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;
        $this->timeRejected = new \DateTimeImmutable();

        return $this;
    }

    public function getTimeRejected(): ?\DateTimeImmutable
    {
        return $this->timeRejected;
    }

    public function setTimeRejected(?\DateTimeImmutable $timeRejected): BusinessTripWorkLog
    {
        $this->timeRejected = $timeRejected;

        return $this;
    }

    public function getRejectionMessage(): ?string
    {
        return $this->rejectionMessage;
    }

    public function setRejectionMessage(?string $rejectionMessage): BusinessTripWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;

        return $this;
    }

    /**
     * @return BusinessTripWorkLogSupport[]
     */
    public function getSupport(): array
    {
        if ($this->support instanceof Collection) {
            return $this->support->toArray();
        }

        return $this->support;
    }

    /**
     * @param BusinessTripWorkLogSupport[]|Collection $support
     */
    public function setSupport(array $support): BusinessTripWorkLog
    {
        $this->support = $support;

        return $this;
    }

    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    /**
     * @return BusinessTripWorkLog
     */
    public function setWorkMonth(WorkMonth $workMonth): WorkLogInterface
    {
        $this->workMonth = $workMonth;

        return $this;
    }

    public function resolveWorkLogMonth(): int
    {
        return (int) $this->date->format('m');
    }

    public function resolveWorkLogYear(): int
    {
        return (int) $this->date->format('Y');
    }
}
