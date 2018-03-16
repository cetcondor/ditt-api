<?php

namespace App\Entity;

class Config
{
    public $id;
    public $title;
    public $description;

    public function __construct()
    {
        $this->title = '';
        $this->description = '';
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
     * @return Config
     */
    public function setId(?int $id): Config
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Config
     */
    public function setTitle(string $title): Config
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Config
     */
    public function setDescription(string $description): Config
    {
        $this->description = $description;

        return $this;
    }
}
