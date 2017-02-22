<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Url;

class UrlRepository extends EntityRepository
{
    const ATTEMPTS_AFTER_ALIAS_LENGTH_INCREASE = 5;

    /**
     * Delete all urls which exists longer than its lifetime
     *
     * @return void
     */
	public function deleteExpired()
	{
		$dateTime = new \DateTime('now');
		$expiredDate = $dateTime->sub(new \DateInterval('P' . Url::LIFETIME_DAYS . 'D'));
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder->delete($this->getEntityName(), 'url')
					 ->where('url.createdAt < :expired_date')
					 ->setParameter('expired_date', $expiredDate)
					 ->getQuery()
					 ->execute();
	}

	/**
     * Retrieve Url Entity with alias
     *
     * @return AppBundle\Entity\Url
     */
	public function shorten($basicUrl, $desiredShortUrl = null)
	{
		while (is_array($basicUrl)) {
			$basicUrl = array_shift($basicUrl);
		}
		if (!empty($basicUrl) && stripos($basicUrl, 'http://') !== 0
			&& stripos($basicUrl, 'https://') !== 0) {
			$basicUrl = 'http://' . $basicUrl;
		}
		$urlEntity = $this->findOneByBasicUrl($basicUrl);
		if (empty($urlEntity)) {
			$entityClassName = $this->getEntityName();
			$urlEntity = new $entityClassName();

			$urlEntity->setBasicUrl($basicUrl);

			while (is_array($desiredShortUrl)) {
				$desiredShortUrl = array_shift($desiredShortUrl);
			}
			$alias = $desiredShortUrl;
			if (empty($alias)) {
				$alias = $this->_generateUniqueUrlAlias();
			}
			$urlEntity->setUrlAlias($alias);
		}
		return $urlEntity;
	}

	/**
     * Retrieve unique url alias
     *
     * @return string
     */
	protected function _generateUniqueUrlAlias()
	{
		$aliasLength = Url::ALIAS_MIN_LENGTH;
		$attempts = 0;
		while (true) {
			$alias = $this->_generateUrlAlias($aliasLength);
			if (!$this->_isAliasUsing($alias)) {
				break;
			}
			if (++$attempts > self::ATTEMPTS_AFTER_ALIAS_LENGTH_INCREASE) {
				$aliasLength++;
				$attempts = 0;
			}
		}
		return $alias;
	}

	/**
     * Retrieve url alias
     *
     * @param int $aliasLength Alias length
     * @return string
     */
	protected function _generateUrlAlias($aliasLength)
	{
		$alphaNumeric = 'abcdefghigklmnopqrstuvwxyz';
		$alphaNumeric .= strtoupper($alphaNumeric);
		$alphaNumeric .= '1234567890';

		$alphaNumericLength = strlen($alphaNumeric);
		$alias = '';
		for ($i = 0; $i < $aliasLength; $i++) {
			$alias .= $alphaNumeric[mt_rand(0, $alphaNumericLength)];
		}
		return $alias;
	}

	/**
     * Check if alias is already using
     *
     * @return bool
     */
	protected function _isAliasUsing($alias)
	{
		$entity = $this->findOneByUrlAlias($alias);
		return !empty($entity);
	}
}
