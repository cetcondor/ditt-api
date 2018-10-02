<?php

namespace App\Entity;

class SickDayWorkLog implements WorkLogInterface
{
    const VARIANT_WITH_NOTE = 'WITH_NOTE';
    const VARIANT_WITHOUT_NOTE = 'WITHOUT_NOTE';
    const VARIANT_SICK_CHILD = 'SICK_CHILD';
    const VARIANTS = [
      self::VARIANT_WITH_NOTE,
      self::VARIANT_WITHOUT_NOTE,
      self::VARIANT_SICK_CHILD,
    ];

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    /**
     * @var string|null
     */
    private $childName;

    /**
     * @var \DateTimeImmutable|null
     */
    private $childDateOfBirth;

    /**
     * @var string
     */
    private $variant;

    /**
     * @var WorkMonth
     */
    private $workMonth;

    public function __construct()
    {
        $this->date = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
        $this->variant = '';
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
     * @return SickDayWorkLog
     */
    public function setId(?int $id): SickDayWorkLog
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
     * @return SickDayWorkLog
     */
    public function setDate(\DateTimeImmutable $date): SickDayWorkLog
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getChildName(): ?string
    {
        return $this->childName;
    }

    /**
     * @param null|string $childName
     * @return SickDayWorkLog
     */
    public function setChildName(?string $childName): SickDayWorkLog
    {
        $this->childName = $childName;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getChildDateOfBirth(): ?\DateTimeImmutable
    {
        return $this->childDateOfBirth;
    }

    /**
     * @param \DateTimeImmutable|null $childDateOfBirth
     * @return SickDayWorkLog
     */
    public function setChildDateOfBirth(?\DateTimeImmutable $childDateOfBirth): SickDayWorkLog
    {
        $this->childDateOfBirth = $childDateOfBirth;

        return $this;
    }

    /**
     * @return string
     */
    public function getVariant(): string
    {
        return $this->variant;
    }

    /**
     * @param string $variant
     * @return SickDayWorkLog
     */
    public function setVariant(string $variant): SickDayWorkLog
    {
        $this->variant = $variant;

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
     * @return SickDayWorkLog
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

    /**
     * @return string[]
     */
    public static function getConstantVariants(): array
    {
        return self::VARIANTS;
    }
}
