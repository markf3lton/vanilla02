<?php
/**
 * Provides a resource to get bundles by entity.
 *
 * @RestResource(
 *   id = "ah_tasking_api",
 *   label = @Translation("Tasking Detail API"),
 *   uri_paths = {
 *     "canonical" = "/ahtasking/{formid}"
 *   }
 * )
 */
namespace Drupal\ah_services\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Drupal\taxonomy\Entity\Term;

class AhTaskingDetails extends ResourceBase {
  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest')
    );
  }
   /*
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing a list of bundle names.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get($formid = NULL) {
    if ($formid) {
      $data = [];
      $data['form'] = [];
      $task = array();
      $cacheable_dependencies = [];
      $data['form'] = $this->getTidByName($formid);
      if (isset($data['form'])) {
        foreach ($data['form'] as $key => $value) {
           if ($key !== 'field_ah_forms_term_const') {
              $task[$key] = $value->value;
           }
           else {
            $task[$key] = $value;
           }
        }
      }
      $data['form'] = $task;
      if (!empty($data)) {
        $response = new ResourceResponse($data);
        return $response;
      }
    }
    return "No Data";
  }

  /**
   * Utility: find term by name and vid.
   * @param null $name
   *  Term name
   * @return JSON
   *  Term structure.
   *
   * @codeCoverageIgnore
   */
  protected function getTidByName($name = NULL, $vid = NULL) {
    $term = null;
    // Get match with priority: exact match, starting with and contains.
    $term_results = \Drupal::database()
      ->query(
        "SELECT
          tid
        FROM taxonomy_term_field_data
        WHERE vid = 'ah_eloqua_tasks'
        AND name LIKE :term_name_like
        ORDER BY INSTR(name,:term_name) ASC, (name = :term_name) DESC, weight ASC LIMIT 0,1",
        [':term_name_like' => '%' . $name . '%', ':term_name' => $name]
      )
      ->fetchAll();
    // Till no match found check for alias names.
    if (count($term_results) == 0) {
      $term_results = \Drupal::database()
        ->query(
          "SELECT
            an.entity_id as tid
          FROM taxonomy_term__field_ah_forms_alias_name an
          JOIN taxonomy_term_field_data t
          ON t.tid = an.entity_id
          WHERE an.field_ah_forms_alias_name_value LIKE :term_name_like
          ORDER BY INSTR(an.field_ah_forms_alias_name_value,:term_name) ASC, (an.field_ah_forms_alias_name_value = :term_name) DESC, t.weight ASC LIMIT 0,1",
          [':term_name_like' => '%' . $name . '%', ':term_name' => $name]
        )
        ->fetchAll();

      // Till no match found return FullRegForm.
      if (count($term_results) == 0) {
        $term_results = \Drupal::database()
          ->select('taxonomy_term_field_data', 'ttfd')
          ->fields('ttfd', ['tid'])
          ->condition('name', 'FullRegForm')
          ->range(0,1)
          ->execute()
          ->fetchAll();
        }
    }
    foreach($term_results as $result) {
      $term = Term::load($result->tid);
    }

    return $term;
  }

}
