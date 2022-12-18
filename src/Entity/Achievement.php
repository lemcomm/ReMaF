<?php 

namespace App\Entity;

class Achievement {

	public function __toString() {
		return "achievement {$this->key} ({$this->value})";
	}

	public function getValue() {
		switch ($this->getType()) {
			case 'battlesize':	return floor(sqrt($this->value));
			default:
				return $this->value;
		}
	}
    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $value;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;


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
     * @param \App\Entity\Character $character
     * @return Achievement
     */
    public function setCharacter(\App\Entity\Character $character = null)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return \App\Entity\Character 
     */
    public function getCharacter()
    {
        return $this->character;
    }
}
