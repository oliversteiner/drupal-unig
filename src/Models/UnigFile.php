<?php

namespace Drupal\unig\Models;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\unig\Utility\AlbumTrait;
use Drupal\unig\Utility\Helper;
use Drupal\unig\Utility\ProjectTrait;
use Drupal\unig\Utility\UnigCache;
use Exception;

class UnigFile
{
  use Drupal\unig\Utility\CreateImageStylesTrait;
  public const type = 'unig_file';

  /* Drupal Fields */
  public const field_album = 'field_unig_album';
  public const field_copyright = 'field_unig_copyright';
  public const field_description = 'field_unig_description';
  public const field_file = 'field_unig_file';
  public const field_image = 'field_unig_image';
  public const field_keywords = 'field_unig_keywords';
  public const field_people = 'field_unig_people';
  public const field_project = 'field_unig_project';
  public const field_rating = 'field_unig_rating';
  public const field_title_generated = 'field_unig_title_generated';
  public const field_trash = 'field_unig_trash';
  public const field_weight = 'field_unig_weight';
  public const field_favorite = 'field_unig_favorit';

  /**
   * @param $file_id
   * @param null $project_id
   * @return array
   * @throws EntityStorageException
   */
  public static function delete($file_id, $project_id = null): array
  {
    $status = false;
    $message = $file_id;

    if ($file_id) {
      $node = Node::Load($file_id);

      // load node
      if ($node) {
        $node->delete();

        // Node delete success
        $status = true;
        $message = t('The file with the ID %file_id was deleted.', [
          '%file_id' => $file_id
        ]);

        // Clear Project Cache
        if ($project_id) {
          UnigCache::clearProjectCache($project_id);
        }
      }
      // no Node found
      else {
        $status = false;
        $message = 'no file found with ID ' . $file_id;
      }
    }

    // Output
    return [
      'status' => $status,
      'message' => $message
    ];
  }

  /**
   * @param $file_id
   * @param $value
   * @param null $project_id
   * @return array
   */
  public static function favorite($file_id, $value, $project_id = null): array
  {
    $status = false;
    $message = '';

    if ($file_id) {
      $node = Node::Load((int) $file_id);

      // load node
      if ($node) {
        $node->set(self::field_favorite, $value);
        try {
          $node->save();
          $status = true;

          // Status Message
          if ($value) {
            $message = t(
              'The file with the ID %file_id was marked as favorite.',
              ['%file_id' => $file_id]
            );
          } else {
            $message = t(
              'The file with the ID %file_id is no longer a favorite.',
              ['%file_id' => $file_id]
            );
          }

          // Clear Project Cache
          if ($project_id) {
            UnigCache::clearProjectCache($project_id);
          }

          // on Error
        } catch (EntityStorageException $e) {
          $status = 'error';
          $message =
            t('Can\'t save changes of File %file_id. ', [
              '%file_id' => $file_id
            ]) . $e;
        }
      }
      // no Node found
      else {
        $status = false;
        $message = 'no file found with ID ' . $file_id;
      }
    }
    // Output
    return [
      'status' => $status,
      'message' => $message
    ];
  }

  /**
   * @param $file_nid
   *
   * @return array
   *
   * @throws InvalidPluginDefinitionException
   * @throws Exception
   */
  public static function buildFile($file_nid): array
  {
    // project
    //  - nid
    //  - date
    //  - timestamp
    //  - title
    //  - description
    //  - copyright

    //  - weight (draggable)
    //  - album
    //      - title
    //      - number_of_items
    //  - links
    //    - edit
    //    - delete

    $entity = Drupal::entityTypeManager()
      ->getStorage('node')
      ->load($file_nid);

    if ($entity && $entity->bundle() !== 'unig_file') {
      $message = 'Node with ' . $file_nid . ' is not an UniG-File';
      \Drupal::logger('type')->error($message);
      return ['nid' => 0];
    }

    // NID
    $nid = $entity->id();

    // Title
    $title = $entity->label();

    // Title Generated
    $title_generated = $entity->get('field_unig_title_generated')->getValue();
    if ($title_generated) {
      $title_generated = $title_generated[0]['value'];
    } else {
      $title_generated = 1;
    }

    // Description
    $description = $entity->get('field_unig_description')->getValue();

    if ($description) {
      $description = $description[0]['value'];
    }

    // comments
    $comments = 'comments';

    // Rating
    $rating = 0;
    $node_rating = $entity->get('field_unig_rating')->getValue();
    if ($node_rating) {
      $rating = $node_rating[0]['value'];
    }

    // Rating
    $favorite = false;
    $node_favorite = $entity->get(self::field_favorite)->getValue();
    if ($node_favorite) {
      $favorite = $node_favorite[0]['value'];
    }

    // Weight
    $weight = 0;
    $node_weight = $entity->get('field_unig_weight')->getValue();
    if ($node_weight) {
      $weight = $node_weight[0]['value'];
    }

    // Copyright
    $copyright = '';
    $node_copyright = $entity->get('field_unig_copyright')->getValue();
    if ($node_copyright) {
      $copyright = $node_copyright[0]['value'];
    }

    // image
    $image = ProjectTrait::getImageVars($file_nid);

    // $file_path = FileSystem::realpath($entity->get('field_unig_image')->entity->getFileUri());
    $file_name = $entity->get('field_unig_image')->entity->getFilename();

    // people
    $people = [];
    $node_people = $entity->get('field_unig_people')->getValue();
    if ($node_people) {
      foreach ($node_people as $term) {
        $tid = $term['target_id'];
        $term = Term::load($tid);

        if ($term) {
          $name = $term->getName();
          $item = ['id' => (int) $tid, 'name' => (string) $name];
          $people[] = $item;
        }
      }
    }
    // keywords
    $keywords = [];
    $node_keywords = $entity->get('field_unig_keywords')->getValue();
    if ($node_keywords) {
      foreach ($node_keywords as $term) {
        $tid = $term['target_id'];
        $term = Term::load($tid);

        if ($term) {
          $name = $term->getName();
          $item = ['id' => (int) $tid, 'name' => (string) $name];
          $keywords[] = $item;
        }
      }
    }
    // Album List
    $album_list = AlbumTrait::getAlbumList($nid);

    // Twig-Variables
    $file = [
      'id' => (int) $nid,
      'title' => $title,
      'description' => $description,
      'album_list' => $album_list,
      'file_name' => $file_name,
      'image' => $image,
      'comments' => $comments,
      'weight' => $weight,
      'rating' => $rating,
      'favorite' => (int) $favorite,
      'copyright' => $copyright,
      'people' => $people,
      'keywords' => $keywords,
      'title_generated' => $title_generated
    ];

    return $file;
  }

  /**
   * @param $unig_file_id
   * @return array|Drupal\Core\Image\Image
   * @throws Exception
   */
  public static function forceImageStyleFromUnigFile($unig_file_id)
  {
    $variables = [];
    $node = Node::load($unig_file_id);
    if ($node) {
      $unig_image_id = Helper::getFieldValue($node, 'unig_image');
      $variables = self::forceImageStyle($unig_image_id, 'large');
    }

    return $variables;
  }
}
