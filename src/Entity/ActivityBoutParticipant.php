<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityBoutParticipant
 */
class ActivityBoutParticipant
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\ActivityBout
     */
    private $bout;

    /**
     * @var \App\Entity\ActivityParticipant
     */
    private $participant;


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
     * Set bout
     *
     * @param \App\Entity\ActivityBout $bout
     * @return ActivityBoutParticipant
     */
    public function setBout(\App\Entity\ActivityBout $bout = null)
    {
        $this->bout = $bout;

        return $this;
    }

    /**
     * Get bout
     *
     * @return \App\Entity\ActivityBout 
     */
    public function getBout()
    {
        return $this->bout;
    }

    /**
     * Set participant
     *
     * @param \App\Entity\ActivityParticipant $participant
     * @return ActivityBoutParticipant
     */
    public function setParticipant(\App\Entity\ActivityParticipant $participant = null)
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * Get participant
     *
     * @return \App\Entity\ActivityParticipant 
     */
    public function getParticipant()
    {
        return $this->participant;
    }
}
