<?php
/**
 * @file
 * Parse the Drupal Release XML.
 */
namespace Nosh\Util;

/**
 * Fetch information from the release history API.
 */
class DrupalReleaseFetcher {
  /**
   * The url to the Drupal release info.
   */
  public $release_url = 'http://updates.drupal.org/release-history';

  /**
   * Get release information for a particular project.
   */
  public function getReleaseInfo($project_name, $api)
  {
    $request_url = $this->release_url . '/' . $project_name . '/' . $api;
    $data = new \SimpleXMLElement($request_url, 0, true);
    $project = new DrupalProject($data, $api);
    return $project;
  }
}

