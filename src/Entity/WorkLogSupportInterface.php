<?php

namespace App\Entity;

interface WorkLogSupportInterface
{
    public function getId(): ?int;

    public function setId(?int $id): WorkLogSupportInterface;

    public function getDateTime(): \DateTimeImmutable;

    public function setDateTime(\DateTimeImmutable $dateTime): WorkLogSupportInterface;

    public function getSupportedBy(): User;

    public function setSupportedBy(User $supportedBy): WorkLogSupportInterface;

    public function getWorkLog(): WorkLogInterface;

    public function setWorkLog(WorkLogInterface $workLog): WorkLogSupportInterface;
}
