<?php

namespace Nosh\Util;

/**
 * Representation of a Drupal project.
 */
class DrupalProject {
    protected $currentRelease, $title, $shortName;
    public function __construct($data, $api_version)
    {
        $this->apiVersion = $api_version;
        $this->parseData($data);
    }

    protected function parseData($data)
    {
        $this->title = (string) $data->title;
        $this->shortName = (string) $data->short_name;
        $this->apiVersion = (string) $data->api_version;
        $this->recommendedMajor = (string) $data->recommended_major;
        $this->projectStatus = (string) $data->project_status;
        $this->link = (string) $data->link;
        $this->releases = array();
        foreach ($data->releases->release as $release) {
            $releaseArr = array(
                'version' => (string) $release->version,
                'major' => (string) $release->version_major,
                'patch' => (string) $release->version_patch,
                'extra' => (string) $release->version_extra,
                'status' => (string) $release->status,
            );
            $this->currentRelease = $this->compareRelease($releaseArr, $this->currentRelease);
            $this->releases[$releaseArr['version']] = $releaseArr;
        }
    }

    public function compareRelease($version1, $version2)
    {
        if ($this->versionHigher($version1, $version2)) {
            return $version1;
        }
        return $version2;
    }

    protected function versionHigher($version1, $version2)
    {
        // Compare major and patch first.
        if (($version1['major'] > $version2['major']) || ($version1['patch'] > $version2['patch'])) {
            return TRUE;
        }
        // We need to have a look at the extras.
        if (!empty($version1['extra'])) {
            // If version 2 doesn't have an extra, it's a higher version.
            if (empty($version2['extra'])) {
                return FALSE;
            }
            $matches = array();
            // The order of the extras. Higher is better.
            $order = array('alpha', 'beta', 'rc', 'dev');
            $pattern = '/(alpha|beta|rc|dev)([0-9]*)/';
            preg_match($pattern, $version1['extra'], $version1_matches);
            preg_match($pattern, $version2['extra'], $version2_matches);

            if (array_search($version1_matches[1], $order) > array_search($version2_matches[1], $order)) {
                return TRUE;
            }
            if ($version1_matches[2] > $version2_matches[2]) {
                return TRUE;
            }
        }
        return FALSE;
    }

    function currentRelease()
    {
        return $this->currentRelease;
    }
}
