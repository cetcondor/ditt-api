<?php

namespace App\Entity;

interface SpecialWorkLogInterface extends WorkLogInterface
{
    public function markApproved(): SpecialWorkLogInterface;

    public function getTimeApproved(): ?\DateTimeImmutable;

    public function setTimeApproved(?\DateTimeImmutable $timeApproved): SpecialWorkLogInterface;

    public function markRejected(string $rejectionMessage): SpecialWorkLogInterface;

    public function getTimeRejected(): ?\DateTimeImmutable;

    public function setTimeRejected(?\DateTimeImmutable $timeRejected): SpecialWorkLogInterface;

    public function getRejectionMessage(): ?string;

    public function setRejectionMessage(?string $rejectionMessage): SpecialWorkLogInterface;

    public function getSupport(): array;

    public function setSupport(array $support): SpecialWorkLogInterface;
}
