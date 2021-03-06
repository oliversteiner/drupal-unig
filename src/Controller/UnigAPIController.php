<?php

namespace Drupal\unig\Controller;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\Timer;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Drupal\unig\Models\UnigFile;
use Drupal\unig\Utility\AlbumTrait;
use Drupal\unig\Utility\ProjectTemplateTrait;
use Drupal\unig\Utility\ProjectTrait;
use Drupal\unig\Utility\UnigCache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class APIController.
 */
class UnigAPIController extends ControllerBase
{
  use projectTemplateTrait;

  /**
   * @param $id
   * @return JsonResponse
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   * @throws EntityStorageException
   */
  public function project($id): JsonResponse
  {
    // Get HTTP Vars
    $post_as_json = \Drupal::request()->getContent();
    // $method = \Drupal::request()->getMethod();

    $data = json_decode($post_as_json, true);

    // CREATE

    // DELETE

    // UPDATE

    // GET

    $label = 'Unig Project Variables';
    $name = 'project';
    $base = 'unig/api/';
    $version = '1.0.0';

    $project = $this->getProjectVariables($id, null);

    $response = [
      'label' => $label,
      'path' => $base . $name,
      'version' => $version,
      'project' => $project
    ];

    return new JsonResponse($response);
  }

  /**
   * Name of our module.
   *
   * @return string
   *   A module name.
   */
  protected function getModuleName(): string
  {
    return 'unig';
  }



  /**
   * @param $file_id
   * @param $project_id
   * @return JsonResponse
   * @throws \Exception
   */
  public function file($file_id, $project_id): JsonResponse
  {
    $label = 'Unig File';
    $name = 'file';
    $base = 'unig/api/';
    $version = '1.0.6';

     $result = UnigFile::buildFile($file_id);


    $response = [
      'label' => $label,
      'path' => $base . $name,
      'version' => $version,
      'project_id' => $project_id,
      'file_id' => $file_id,
      'file' => $result,
    ];

    return new JsonResponse($response);
  }

  /**
   * @param $file_id
   * @param $project_id
   * @return JsonResponse
   * @Method("DELETE")
   * @Route(unig.api.file.delete)
   * @throws EntityStorageException
   */
  public function fileDelete($file_id, $project_id): JsonResponse
  {

    $result = UnigFile::delete($file_id, $project_id);

    return new JsonResponse($result);
  }

  /**
   * @param $file_id
   * @param $value
   * @param $project_id
   * @return JsonResponse
   */
  public function fileFavorite($file_id, $value, $project_id): JsonResponse
  {
    $label = 'Set Favorite of File';
    $name = 'favorite';
    $base = 'unig/api/file';
    $version = '1.0.0';
    $favorite = false;

    $result = UnigFile::favorite($file_id, $value, $project_id);

    if($result['status']){
      $favorite = $value;
    }

    $response = [
      'label' => $label,
      'path' => $base . $name,
      'version' => $version,
      'projectId' => (int)$project_id,
      'favorite' => (int)$favorite,
      'id' => (int)$file_id,
      'status' => $result['status'],
      'message' => $result['message'],
    ];


    return new JsonResponse($response);
  }

  /**
   * @param $id
   * @return JsonResponse
   * @throws EntityStorageException
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public function cacheRebuild($id): JsonResponse
  {
    $label = 'Unig Project Rebuild Clear Cache';
    $name = 'cache-rebuild';
    $base = 'unig/api/';
    $version = '1.0.0';


    Timer::start($name);

    UnigCache::clearProjectCache($id);
    $variables = [];
    $variables['album'] = AlbumTrait::getAlbumList($id);
    $variables['project'] = ProjectTrait::buildProject($id);
    $variables['files'] = ProjectTrait::buildFileList($id, null);
    $variables['keywords'] = ProjectTrait::getKeywordTerms($id);
    $variables['people'] = ProjectTrait::getPeopleTerms($id);
    $result = UnigCache::saveProjectCache($id, $variables);

    Timer::stop($name);
    $timer = Timer::read($name);

    $response = [
      'label' => $label,
      'path' => $base . $name,
      'version' => $version,
      'projectId' => $id,
      'cache-rebuild' => $result,
      'timer' => $timer,
      'variables' =>$variables,
    ];

    return new JsonResponse($response);
  }

  /**
   * @param $id
   * @return JsonResponse
   * @throws EntityStorageException
   */
  public function cacheClear($id): JsonResponse
  {
    $label = 'Unig Project Clear Cache';
    $name = 'cache-clear';
    $base = 'unig/api/';
    $version = '1.0.0';

    Timer::start($name);
    $result = UnigCache::clearProjectCache($id);
    Timer::stop($name);
    $timer = Timer::read($name);

    $response = [
      'label' => $label,
      'path' => $base . $name,
      'version' => $version,
      'projectId' => $id,
      'cache-clear' => $result,
      'timer' => $timer,

    ];

    return new JsonResponse($response);
  }

}


