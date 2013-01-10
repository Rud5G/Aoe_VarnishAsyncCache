<?php

class Aoe_VarnishAsyncCache_Model_AsyncCacheObserver {

	public function postProcessJobCollection(Mage_Core_Model_Observer $observer) {
		$jobCollection = $observer->getJobCollection(); /* @var $jobCollection Aoe_AsyncCache_Model_JobCollection */

		foreach ($jobCollection as $job) { /* @var $job Aoe_AsyncCache_Model_Job */
			if (!$job->getIsProcessed() && $job->getMode() == Aoe_VarnishAsyncCache_Helper_Data::MODE_PURGEVARNISHURL) {

				$startTime = time();
				$errors = Mage::helper('varnishasynccache')->purgeVarnishUrls($job->getTags());
				$job->setDuration(time() - $startTime);
				$job->setIsProcessed(true);

				if (!empty($errors)) {
					foreach ($errors as $error) {
						Mage::log($error);
					}
				}

				Mage::log(sprintf('[ASYNCCACHE] MODE: %s, DURATION: %s sec, TAGS: %s',
					$job->getMode(),
					$job->getDuration(),
					implode(', ', $job->getTags())
				));

				$job->setIsProcessed(true);
			}
		}
	}

}