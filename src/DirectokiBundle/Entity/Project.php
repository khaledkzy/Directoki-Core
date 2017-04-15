<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity()
 * @ORM\Table(name="project")
 * @ORM\HasLifecycleCallbacks
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="public_id", type="string", length=250, unique=true, nullable=false)
     * @Assert\NotBlank()
     */
    protected $publicId;

    /**
     * @ORM\ManyToOne(targetEntity="JMBTechnology\UserAccountsBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=250, nullable=false)
     */
    protected $title;


    /**
     * @var boolean
     *
     * @ORM\Column(name="is_api_read_allowed", type="boolean", nullable=false)
     */
    protected  $APIReadAllowed = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_api_moderated_edit_allowed", type="boolean", nullable=false)
     */
    protected  $APIModeratedEditAllowed = true;


    /**
     * @var boolean
     *
     * @ORM\Column(name="is_api_report_allowed", type="boolean", nullable=false)
     */
    protected  $APIReportAllowed = true;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

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
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $publicId
     */
    public function setPublicId($publicId)
    {
        $this->publicId = $publicId;
    }

    /**
     * @return mixed
     */
    public function getPublicId()
    {
        return $this->publicId;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt( $createdAt ) {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner( $owner ) {
        $this->owner = $owner;
    }

    /**
     * @return boolean
     */
    public function isAPIModeratedEditAllowed() {
        return $this->APIModeratedEditAllowed;
    }

    /**
     * @param boolean $APIModeratedEditAllowed
     */
    public function setAPIModeratedEditAllowed( $APIModeratedEditAllowed ) {
        $this->APIModeratedEditAllowed = $APIModeratedEditAllowed;
    }

    /**
     * @return boolean
     */
    public function isAPIReadAllowed() {
        return $this->APIReadAllowed;
    }

    /**
     * @param boolean $APIReadAllowed
     */
    public function setAPIReadAllowed( $APIReadAllowed ) {
        $this->APIReadAllowed = $APIReadAllowed;
    }

    /**
     * @return boolean
     */
    public function isAPIReportAllowed() {
        return $this->APIReportAllowed;
    }

    /**
     * @param boolean $APIReportAllowed
     */
    public function setAPIReportAllowed( $APIReportAllowed ) {
        $this->APIReportAllowed = $APIReportAllowed;
    }




    /**
     * @ORM\PrePersist()
     */
    public function beforeFirstSave() {
        $this->createdAt = new \DateTime("", new \DateTimeZone("UTC"));
    }


}

