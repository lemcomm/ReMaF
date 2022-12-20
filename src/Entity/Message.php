<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 */
class Message
{
    /**
     * @var string
     */
    private $topic;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $sent;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var string
     */
    private $system_content;

    /**
     * @var string
     */
    private $content;

    /**
     * @var integer
     */
    private $recipient_count;

    /**
     * @var string
     */
    private $target;

    /**
     * @var boolean
     */
    private $read;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $replies;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tags;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $recipients;

    /**
     * @var \App\Entity\Conversation
     */
    private $conversation;

    /**
     * @var \App\Entity\Character
     */
    private $sender;

    /**
     * @var \App\Entity\Message
     */
    private $reply_to;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->replies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->recipients = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set topic
     *
     * @param string $topic
     * @return Message
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return string 
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Message
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
     * Set sent
     *
     * @param \DateTime $sent
     * @return Message
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent
     *
     * @return \DateTime 
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return Message
     */
    public function setCycle($cycle)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     *
     * @return integer 
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     * Set system_content
     *
     * @param string $systemContent
     * @return Message
     */
    public function setSystemContent($systemContent)
    {
        $this->system_content = $systemContent;

        return $this;
    }

    /**
     * Get system_content
     *
     * @return string 
     */
    public function getSystemContent()
    {
        return $this->system_content;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Message
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set recipient_count
     *
     * @param integer $recipientCount
     * @return Message
     */
    public function setRecipientCount($recipientCount)
    {
        $this->recipient_count = $recipientCount;

        return $this;
    }

    /**
     * Get recipient_count
     *
     * @return integer 
     */
    public function getRecipientCount()
    {
        return $this->recipient_count;
    }

    /**
     * Set target
     *
     * @param string $target
     * @return Message
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return string 
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set read
     *
     * @param boolean $read
     * @return Message
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * Get read
     *
     * @return boolean 
     */
    public function getRead()
    {
        return $this->read;
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
     * Add replies
     *
     * @param \App\Entity\Message $replies
     * @return Message
     */
    public function addReply(\App\Entity\Message $replies)
    {
        $this->replies[] = $replies;

        return $this;
    }

    /**
     * Remove replies
     *
     * @param \App\Entity\Message $replies
     */
    public function removeReply(\App\Entity\Message $replies)
    {
        $this->replies->removeElement($replies);
    }

    /**
     * Get replies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Add tags
     *
     * @param \App\Entity\MessageTag $tags
     * @return Message
     */
    public function addTag(\App\Entity\MessageTag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \App\Entity\MessageTag $tags
     */
    public function removeTag(\App\Entity\MessageTag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add recipients
     *
     * @param \App\Entity\MessageRecipient $recipients
     * @return Message
     */
    public function addRecipient(\App\Entity\MessageRecipient $recipients)
    {
        $this->recipients[] = $recipients;

        return $this;
    }

    /**
     * Remove recipients
     *
     * @param \App\Entity\MessageRecipient $recipients
     */
    public function removeRecipient(\App\Entity\MessageRecipient $recipients)
    {
        $this->recipients->removeElement($recipients);
    }

    /**
     * Get recipients
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Set conversation
     *
     * @param \App\Entity\Conversation $conversation
     * @return Message
     */
    public function setConversation(\App\Entity\Conversation $conversation = null)
    {
        $this->conversation = $conversation;

        return $this;
    }

    /**
     * Get conversation
     *
     * @return \App\Entity\Conversation 
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * Set sender
     *
     * @param \App\Entity\Character $sender
     * @return Message
     */
    public function setSender(\App\Entity\Character $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \App\Entity\Character 
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set reply_to
     *
     * @param \App\Entity\Message $replyTo
     * @return Message
     */
    public function setReplyTo(\App\Entity\Message $replyTo = null)
    {
        $this->reply_to = $replyTo;

        return $this;
    }

    /**
     * Get reply_to
     *
     * @return \App\Entity\Message 
     */
    public function getReplyTo()
    {
        return $this->reply_to;
    }

    public function isRead(): ?bool
    {
        return $this->read;
    }
}
