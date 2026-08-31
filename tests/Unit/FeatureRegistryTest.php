<?php
/**
 * The switches, and what the add-on is allowed to do to them.
 *
 * @package BlueFairy\StoreAccelerator
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator\Tests\Unit;

use BlueFairy\StoreAccelerator\FeatureRegistry;
use PHPUnit\Framework\TestCase;

final class FeatureRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['bfsa_test_options'] = [];
        $GLOBALS['bfsa_test_filters']  = [];
    }

    public function test_a_feature_with_no_stored_value_is_on(): void
    {
        $this->assertTrue(FeatureRegistry::is_enabled('scripts'));
    }

    /**
     * The add-on's features arrive after the option was last written. Defaulting
     * an unknown key to off would silently disable everything a customer just
     * paid for, with no visible cause.
     */
    public function test_a_feature_the_stored_option_predates_is_on(): void
    {
        $GLOBALS['bfsa_test_options'][FeatureRegistry::OPTION] = ['scripts' => true, 'styles' => true];

        $this->assertTrue(FeatureRegistry::is_enabled('images'));
    }

    public function test_a_feature_stored_as_off_is_off(): void
    {
        $GLOBALS['bfsa_test_options'][FeatureRegistry::OPTION] = ['scripts' => false];

        $this->assertFalse(FeatureRegistry::is_enabled('scripts'));
    }

    /**
     * 1.0.4 listed a 'cache' key in its allow-list that the settings form never
     * rendered, so the first save wrote it off for ever. sanitize() must build
     * from the label list, which is the same list the form renders from.
     */
    public function test_every_key_stored_is_a_key_with_a_label(): void
    {
        $this->assertSame(
            array_keys(FeatureRegistry::labels()),
            array_keys(FeatureRegistry::sanitize(['scripts' => '1']))
        );
    }

    public function test_an_unknown_posted_key_is_discarded(): void
    {
        $this->assertArrayNotHasKey('smuggled', FeatureRegistry::sanitize(['smuggled' => '1']));
    }

    public function test_an_unchecked_box_is_stored_as_off(): void
    {
        $this->assertFalse(FeatureRegistry::sanitize(['scripts' => '1'])['styles']);
    }

    /**
     * The add-on contributes through this filter; if a label arrives without a
     * matching description the settings page renders a blank cell.
     */
    public function test_a_filtered_in_feature_becomes_storable(): void
    {
        $GLOBALS['bfsa_test_filters']['bfsa_feature_labels'] = static fn(array $l): array => $l + ['images' => 'Images'];

        $this->assertArrayHasKey('images', FeatureRegistry::sanitize(['images' => '1']));
        $this->assertTrue(FeatureRegistry::sanitize(['images' => '1'])['images']);
    }
}
