<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
abstract class BaseRecordHasFieldValueMultilingual extends  BaseRecordHasFieldValue
{


    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=250, nullable=false)
     */
    protected $locale = 'en_GB';

    /**
     * @return string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale( $locale ) {
        $this->locale = $locale;
    }

}


