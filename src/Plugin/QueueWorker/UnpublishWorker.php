<?php

namespace Drupal\content_unpublisher\Plugin\QueueWorker;

/**
 * Class UnpublishWorker
 * @package Drupal\content_unpublisher\Plugin\QueueWorker
 *
 * @QueueWorker(
 *   id = "unpublish_queue",
 *   title = "Queue worker for unpublishing expired content",
 *   cron = {"time" = 20}
 * )
 */
class UnpublishWorker extends UnpublishWorkerBase {
  public function processItem($data)
  {
    // TODO: Implement processItem() method.
    $this->unpublishExpiredContents();
  }
}