<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity(repositoryClass="DirectokiBundle\Repository\RecordHasFieldEmailValueRepository")
 * @ORM\Table(name="record_has_field_email_value")
 * @ORM\HasLifecycleCallbacks
 */
class RecordHasFieldEmailValue extends BaseRecordHasFieldValueMultilingual
{



    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    protected $value;



    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


}

