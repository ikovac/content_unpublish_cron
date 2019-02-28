<?php

namespace Drupal\content_unpublisher\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WebDriver\Exception;

abstract class UnpublishWorkerBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  private $entity_query;
  private $entity_manager;
  private $logger;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryFactory $entity_query, EntityManagerInterface $entity_manager, LoggerChannelFactoryInterface $logger)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entity_query = $entity_query;
    $this->entity_manager = $entity_manager;
    $this->logger = $logger;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    // TODO: Implement create() method.
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query'),
      $container->get('entity.manager'),
      $container->get('logger.factory')
    );
  }

  protected function unpublishExpiredContents() {
    $query = $this->entity_query->get('node')
      ->condition('status', 1)
      ->condition('field_datum_isteka', date('Y-m-d'), '<=');
    $nids = $query->execute();

    $node_storage = $this->entity_manager->getStorage('node');
    try {
      foreach ($nids as $nid) {
        $node_loaded = $node_storage->load($nid);
        $node_loaded->status->value = 0;
        $node_loaded->save();
      }
    } catch (Exception $e) {
      throw $e;
    }
    $this->logger->get('content_unpublisher')->info('Queue unpublish_content worker created at @time', [
      '@time' => date_iso8601(REQUEST_TIME),
    ]);
  }
}