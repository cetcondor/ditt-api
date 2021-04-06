<?php

namespace App\Entity;

trait SpecialWorkLogSupportTrait
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     */
    private $dateTime;

    /**
     * @var User
     */
    private $supportedBy;

    /**
     * @var WorkLogInterface
     */
    private $workLog;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): SpecialWorkLogSupportInterface
    {
        $this->id = $id;

        return $this;
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeImmutable $dateTimeTime): SpecialWorkLogSupportInterface
    {
        $this->dateTime = $dateTimeTime;

        return $this;
    }

    public function getSupportedBy(): User
    {
        return $this->supportedBy;
    }

    public function setSupportedBy(User $supportedBy): SpecialWorkLogSupportInterface
    {
        $this->supportedBy = $supportedBy;

        return $this;
    }

    public function getWorkLog(): WorkLogInterface
    {
        return $this->workLog;
    }

    public function setWorkLog(WorkLogInterface $workLog): SpecialWorkLogSupportInterface
    {
        $this->workLog = $workLog;

        return $this;
    }
}
