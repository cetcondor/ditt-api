<?php

namespace App\Entity;

class BusinessTripWorkLog implements WorkLogInterface
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
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return BusinessTripWorkLog
     */
    public function setId(?int $id): BusinessTripWorkLog
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable $date
     * @return BusinessTripWorkLog
     */
    public function setDate(\DateTimeImmutable $date): BusinessTripWorkLog
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getPurpose(): string
    {
        return $this->purpose;
    }

    /**
     * @param string $purpose
     * @return BusinessTripWorkLog
     */
    public function setPurpose(string $purpose): BusinessTripWorkLog
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     * @return BusinessTripWorkLog
     */
    public function setDestination(string $destination): BusinessTripWorkLog
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransport(): string
    {
        return $this->transport;
    }

    /**
     * @param string $transport
     * @return BusinessTripWorkLog
     */
    public function setTransport(string $transport): BusinessTripWorkLog
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpectedDeparture(): string
    {
        return $this->expectedDeparture;
    }

    /**
     * @param string $expectedDeparture
     * @return BusinessTripWorkLog
     */
    public function setExpectedDeparture(string $expectedDeparture): BusinessTripWorkLog
    {
        $this->expectedDeparture = $expectedDeparture;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpectedArrival(): string
    {
        return $this->expectedArrival;
    }

    /**
     * @param string $expectedArrival
     * @return BusinessTripWorkLog
     */
    public function setExpectedArrival(string $expectedArrival): BusinessTripWorkLog
    {
        $this->expectedArrival = $expectedArrival;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimeApproved(): ?\DateTimeImmutable
    {
        return $this->timeApproved;
    }

    /**
     * @param \DateTimeImmutable|null $timeApproved
     * @return BusinessTripWorkLog
     */
    public function setTimeApproved(?\DateTimeImmutable $timeApproved): BusinessTripWorkLog
    {
        $this->timeApproved = $timeApproved;

        return $this;
    }

    /**
     * @return BusinessTripWorkLog
     */
    public function markApproved(): BusinessTripWorkLog
    {
        $this->timeApproved = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @param string $rejectionMessage
     * @return BusinessTripWorkLog
     */
    public function markRejected(string $rejectionMessage): BusinessTripWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;
        $this->timeRejected = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimeRejected(): ?\DateTimeImmutable
    {
        return $this->timeRejected;
    }

    /**
     * @param \DateTimeImmutable|null $timeRejected
     * @return BusinessTripWorkLog
     */
    public function setTimeRejected(?\DateTimeImmutable $timeRejected): BusinessTripWorkLog
    {
        $this->timeRejected = $timeRejected;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRejectionMessage(): ?string
    {
        return $this->rejectionMessage;
    }

    /**
     * @param null|string $rejectionMessage
     * @return BusinessTripWorkLog
     */
    public function setRejectionMessage(?string $rejectionMessage): BusinessTripWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;

        return $this;
    }

    /**
     * @return WorkMonth
     */
    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    /**
     * @param WorkMonth $workMonth
     * @return BusinessTripWorkLog
     */
    public function setWorkMonth(WorkMonth $workMonth): WorkLogInterface
    {
        $this->workMonth = $workMonth;

        return $this;
    }

    /**
     * @return int
     */
    public function resolveWorkLogMonth(): int
    {
        return (int) $this->date->format('m');
    }

    /**
     * @return int
     */
    public function resolveWorkLogYear(): int
    {
        return (int) $this->date->format('Y');
    }
}
