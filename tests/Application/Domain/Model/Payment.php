<?php
namespace Terrazza\Component\Injector\Tests\Application\Domain\Model;

use DateTime;

class Payment {
    private int $id;
    private DateTime $created;
    private float $amount;

    protected function __construct(int $id, DateTime $created, float $amount) {
        $this->id = $id;
        $this->created = $created;
        $this->amount = $amount;
    }

    public static function create(int $id, float $amount) : self {
        return new self(
            $id,
            new DateTime(),
            $amount
        );
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    function getAmount() : float {
        return $this->amount;
    }
}