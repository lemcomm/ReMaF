<?php 

namespace App\Entity;

class Item {

# Nothing to see here, civilian. Move along.

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\ItemType
     */
    private $type;


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
     * Set type
     *
     * @param \App\Entity\ItemType $type
     * @return Item
     */
    public function setType(\App\Entity\ItemType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\ItemType 
     */
    public function getType()
    {
        return $this->type;
    }
}
