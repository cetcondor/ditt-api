<?php

namespace App\Entity;

interface SpecialWorkLogSupportInterface
{
    public function getId(): ?int;

    public function setId(?int $id): SpecialWorkLogSupportInterface;

    public function getDateTime(): \DateTimeImmutable;

    public function setDateTime(\DateTimeImmutable $dateTime): SpecialWorkLogSupportInterface;

    public function getSupportedBy(): User;

    public function setSupportedBy(User $supportedBy): SpecialWorkLogSupportInterface;

    public function getWorkLog(): WorkLogInterface;

    public function setWorkLog(WorkLogInterface $workLog): SpecialWorkLogSupportInterface;
}
