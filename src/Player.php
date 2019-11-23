<?php
namespace DanAbrey\ESPNNCAAStatsScraper;

class Player
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $position;

    /**
     * @var string
     */
    protected $college;

    /**
     * @var array
     */
    protected $seasons = [];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getSeasons(): array
    {
        return $this->seasons;
    }

    /**
     * @param array $seasons
     */
    public function setSeasons(array $seasons): void
    {
        $this->seasons = $seasons;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getCollege(): string
    {
        return $this->college;
    }

    /**
     * @param string $college
     */
    public function setCollege(string $college): void
    {
        $this->college = $college;
    }
}
