<?php

namespace Nosh\Tests\Util;
use Nosh\Util\DrupalReleaseFetcher;

/**
 * Test the Drupal Project class.
 */
class DrupalReleaseFetcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parsing the project xml file.
     */
    function testParseProjectXML()
    {
        // Get release information for drupal core, if that project goes away,
        // we´re in trouble =).
        $fetcher = new DrupalReleaseFetcher();
        $project = $fetcher->getReleaseInfo('drupal', '7.x');
        // We should get a DrupalProject instance.
        $this->assertEquals($project->shortName, 'drupal');
    }
}
