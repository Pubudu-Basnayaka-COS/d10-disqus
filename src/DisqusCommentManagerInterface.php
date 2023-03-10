<?php

namespace Drupal\disqus;

/**
 * It contains common functions to manage disqus_comment fields.
 */
interface DisqusCommentManagerInterface {

  /**
   * Utility function to return an array of disqus_comment fields.
   *
   * @param string $entity_type_id
   *   The content entity type to which the disqus_comment fields are attached.
   *
   * @return array
   *   An array of disqu...www/html/d8/modules/disqus/src
   *   /DisqusCommentManagerInterface.phps_comment field map definitions, keyed
   *   by field name.
   *   Each value is an array with two entries:
   *   - type: The field type.
   *   - bundles: The bundles in which field appears, as an array with entity
   *     types as keys and ...www/html/d8/modules/disqus/src
   *   /DisqusCommentManagerInterface.phpthe array of bundle names as values.
   *
   * @see \Drupal\Core\Entity\EntityManagerInterface::getFieldMap()
   */
  public function getFields($entity_type_id);

  /**
   * Utility function to return all disqus_comment fields.
   */
  public function getAllFields();

  /**
   * Computes the full settings associated with Disqus SSO.
   *
   * These need to be merged into the settings for basic Disqus integration for
   * actual usage.
   *
   * @return array
   *   An array of the ssoSettings.
   */
  public function ssoSettings();

  /**
   * API No action on entity delete.
   */
  const DISQUS_API_NO_ACTION = 0;

  /**
   * API Close on entity delete.
   */
  const DISQUS_API_CLOSE = 1;

  /**
   * API Remove on entity delete.
   */
  const DISQUS_API_REMOVE = 2;

}
