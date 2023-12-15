<?php 

namespace App\Entity;

class Achievement {

	private string $type;
	private int $value;
	private int $id;
	private Character $character;

	public function __toString() {
		return "achievement $this->type ($this->value)";
	}

	public function getValue() {
		return match ($this->getType()) {
			'battlesize' => floor(sqrt($this->value)),
			default => $this->value,
		};
	}


    /**
     * Set type
     *
     * @param string $type
     * @return Achievement
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value
     *
     * @param integer $value
     * @return Achievement
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set character
     *
     * @param Character $character
     *
     * @return Achievement
     */
    public function setCharacter(Character $character = null)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return Character
     */
    public function getCharacter()
    {
        return $this->character;
    }
}
