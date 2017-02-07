<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity(repositoryClass="DirectokiBundle\Repository\RecordRepository")
 * @ORM\Table(name="record", uniqueConstraints={@ORM\UniqueConstraint(name="record_public_id", columns={"directory_id", "public_id"})})
 * @ORM\HasLifecycleCallbacks
 */
class Record
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;



    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Directory")
     * @ORM\JoinColumn(name="directory_id", referencedColumnName="id", nullable=false)
     */
    private $directory;


    /**
     * @ORM\Column(name="public_id", type="string", length=250, unique=false, nullable=false)
     * @Assert\NotBlank()
     */
    private $publicId;



    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="creation_event_id", referencedColumnName="id", nullable=false)
     */
    protected $creationEvent;

    /**
     * @var string
     *
     * @ORM\Column(name="cached_state", type="text", nullable=true)
     */
    protected $cachedState;


    /**
     * @ORM\OneToMany(targetEntity="DirectokiBundle\Entity\RecordHasFieldStringValue", mappedBy="record")
     */
    private $recordHasFieldStringValues;

    /**
     * @ORM\OneToMany(targetEntity="DirectokiBundle\Entity\RecordHasFieldTextValue", mappedBy="record")
     */
    private $recordHasFieldTextValues;

    /**
     * @ORM\OneToMany(targetEntity="DirectokiBundle\Entity\RecordHasFieldBooleanValue", mappedBy="record")
     */
    private $recordHasFieldBooleanValues;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDirectory() {
        return $this->directory;
    }

    /**
     * @param mixed $directory
     */
    public function setDirectory( $directory ) {
        $this->directory = $directory;
    }


    /**
     * @return mixed
     */
    public function getPublicId()
    {
        return $this->publicId;
    }

    /**
     * @param mixed $publicId
     */
    public function setPublicId($publicId)
    {
        $this->publicId = $publicId;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getCreationEvent() {
        return $this->creationEvent;
    }

    /**
     * @param mixed $creationEvent
     */
    public function setCreationEvent( $creationEvent ) {
        $this->creationEvent = $creationEvent;
    }

    /**
     * @return string
     */
    public function getCachedState()
    {
        return $this->cachedState;
    }

    /**
     * @param string $cachedState
     */
    public function setCachedState($cachedState)
    {
        $this->cachedState = $cachedState;
    }






    /**
     * @ORM\PrePersist()
     */
    public function beforeFirstSave() {
        $this->createdAt = new \DateTime("", new \DateTimeZone("UTC"));
    }


}


