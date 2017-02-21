<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="url")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UrlRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity("urlAlias", message="Url alias is already taken")
 * @UniqueEntity("basicUrl")
 */
class Url
{
    const ALIAS_MIN_LENGTH = 3;
    const LIFETIME_DAYS = 15;
    const ALLOWED_HTTP_CODES = [200];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url_alias", type="string", length=16, unique=true)
     *
     * @Assert\NotNull(message="Shorten url should not be null")
     * @Assert\Length(max=16, maxMessage="Url alias is too long (max length = 16)")
     * @Assert\Type(type="alnum", message="Url alias should contain only letters and numbers")
     */
    private $urlAlias;

    /**
     * @var string
     *
     * @ORM\Column(name="basic_url", type="string", length=1024, unique=true)
     *
     * @Assert\NotNull(message="Basic url should not be null")
     * @Assert\Length(max=1024, maxMessage="Basic url is too long (max length = 1024)")
     */
    private $basicUrl;

    /**
     * @var int
     *
     * @ORM\Column(name="usages_count", type="integer")
     */
    private $usagesCount = 0;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (!$this->_isBasicUrlValid()) {
            $context->buildViolation('Basic url is not valid')
                    ->atPath('basicUrl')->addViolation();
        }
    }

    /**
     * Check if basic url is valid
     *
     * @return bool
     */
    protected function _isBasicUrlValid()
    {
        $basicUrl = $this->getBasicUrl();
        if (empty($basicUrl)) {
            return false;
        }
        $ch = curl_init($basicUrl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $responseHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errCode = curl_errno($ch);
        curl_close($ch);

        return !$errCode && in_array($responseHttpCode, self::ALLOWED_HTTP_CODES);
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
     * Set basicUrl
     *
     * @param string $basicUrl
     * @return Url
     */
    public function setBasicUrl($basicUrl)
    {
        $this->basicUrl = $basicUrl;

        return $this;
    }

    /**
     * Get basicUrl
     *
     * @return string 
     */
    public function getBasicUrl()
    {
        return $this->basicUrl;
    }

    /**
     * Set urlAlias
     *
     * @param string $urlAlias
     * @return Url
     */
    public function setUrlAlias($urlAlias)
    {
        $this->urlAlias = $urlAlias;

        return $this;
    }

    /**
     * Get urlAlias
     *
     * @return string 
     */
    public function getUrlAlias()
    {
        return $this->urlAlias;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Url
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Initialize createdAt by current datetime value
     *
     * @ORM\PrePersist
     * @return void 
     */
    public function initCreatedAt()
    {
        if (is_null($this->getCreatedAt())) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    /**
     * Set usagesCount
     *
     * @param integer $usagesCount
     * @return Url
     */
    public function setUsagesCount($usagesCount)
    {
        $this->usagesCount = $usagesCount;

        return $this;
    }

    /**
     * Get usagesCount
     *
     * @return integer 
     */
    public function getUsagesCount()
    {
        return $this->usagesCount;
    }

    /**
     * Increase usagesCount
     *
     * @return Url 
     */
    public function increaseUsagesCount()
    {
        $usagesCount = $this->getUsagesCount();
        $this->setUsagesCount(++$usagesCount);
        return $this;
    }
}
