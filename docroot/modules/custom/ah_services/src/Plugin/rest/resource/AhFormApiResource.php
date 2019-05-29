<?php
/**
 * Provides a resource to get bundles by entity.
 *
 * @RestResource(
 *   id = "ah_form_api",
 *   label = @Translation("Webform Configuration API"),
 *   uri_paths = {
 *     "canonical" = "/ahformapi/{formid}"
 *   }
 * )
 */
namespace Drupal\ah_services\Plugin\rest\resource;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\ah_services\Services\CacheResponse;
use Drupal\Core\Utility\Token;
use Drupal\block_content\Entity\BlockContent;

class AhFormApiResource extends ResourceBase {
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
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityManagerInterface $entity_manager,
    CacheResponse $cache_response,
    Token $token_service,
    $ah_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->entityManager = $entity_manager;
    $this->cacheResponse = $cache_response;
    $this->tokenService = $token_service;
    $this->bubbleableMetadata = new BubbleableMetadata();
    $this->ahService = $ah_service;
  }

  /**
   *  A instance of entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity.manager'),
      $container->get('ah_services.cache_response'),
      $container->get('token'),
      $container->get('ah_services.webform')
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
      // Decode the formId.
      $formid = (!is_int($formid) && !is_numeric($formid) ? base64_decode($formid) :  $formid  );
      $data = [];
      $data['form'] = [];
      $cacheable_dependencies = [];
      $data['form'] = $this->ahService->getFormArray($formid, $cacheable_dependencies);
      $data['time'] = time();
      // Clear cache based on request header and tags.
      $this->cacheResponse->clearCache(['block_content:' . $formid]);

      if (!empty($data)) {
          $response = new ResourceResponse($data);
          $this->cacheResponse->addCacheDependencies($response, $cacheable_dependencies);
          return $response;
      }
    } else {
      return "No Data";
    }
  }
}
