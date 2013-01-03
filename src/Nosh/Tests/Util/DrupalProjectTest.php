<?php

namespace Nosh\Tests\Util;
use Nosh\Util\DrupalProject;

/**
 * Test the Drupal Project class.
 */
class DrupalProjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parsing the project xml file.
     */
    function testParseProjectXML()
    {
        // We load the file from our local directory.
        $data = simplexml_load_file(__DIR__ . '/project.xml');
        $project = new DrupalProject($data, '7.x');
        // Get the basics right.
        $this->assertEquals($project->title, 'Drupal core');
        $this->assertEquals($project->shortName, 'drupal');
        $this->assertEquals($project->recommendedMajor, "7");
        $this->assertEquals($project->projectStatus, "published");
        $this->assertEquals($project->link, "http://drupal.org/project/drupal");
        // Check that the current release is the right one, 7.17.
        $this->assertEquals($project->current['version'], '7.17');
        $this->releases = array();        
    }
}
