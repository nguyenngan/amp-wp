<?php
/**
 * Tests for CallbackReflection class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\CallbackReflection;
use AmpProject\AmpWP\DevTools\FileReflection;
use AmpProject\AmpWP\PluginRegistry;
use ReflectionFunction;
use ReflectionMethod;
use WP_UnitTestCase;

/**
 * Tests for CallbackReflection class.
 *
 * @since 2.0.2
 *
 * @coversDefaultClass \AmpProject\AmpWP\DevTools\CallbackReflection
 */
class CallbackReflectionTest extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var CallbackReflection
	 */
	private $callback_reflection;

	public function setUp() {
		parent::setUp();

		$plugin_registry = new PluginRegistry();
		$file_reflection = new FileReflection( $plugin_registry );
		$file_reflection->register();

		$this->callback_reflection = new CallbackReflection( $file_reflection );

		$theme_root = dirname( dirname( __DIR__ ) ) . '/data/themes';
		add_filter(
			'theme_root',
			static function () use ( $theme_root ) {
				return $theme_root;
			}
		);
		register_theme_directory( $theme_root );
		switch_theme( 'custom' );
	}

	/** @return array */
	public function data_get_source() {
		require_once ABSPATH . '/wp-admin/includes/widgets.php';
		require_once dirname( dirname( __DIR__ ) ) . '/data/themes/custom/functions.php';
		return [
			'theme_function'                      => [
				'my_custom_after_setup_theme',
				'my_custom_after_setup_theme',
				'custom',
				'theme',
				'functions.php',
				ReflectionFunction::class,
			],
			'plugin_function'                     => [
				'amp_after_setup_theme',
				'amp_after_setup_theme',
				'amp',
				'plugin',
				'includes/amp-helper-functions.php',
				ReflectionFunction::class,
			],
			'core_includes_wp_scripts_method'     => [
				'WP_Scripts::print_scripts',
				'WP_Scripts::print_scripts',
				'wp-includes',
				'core',
				'class.wp-scripts.php',
				ReflectionMethod::class,
			],
			'core_widget_display_callback_array'  => [
				[ 'WP_Widget_Text', 'display_callback' ],
				'WP_Widget_Text::widget',
				'wp-includes',
				'core',
				'widgets/class-wp-widget-text.php',
				ReflectionMethod::class,
			],
			'core_widget_display_callback_string' => [
				'WP_Widget_Text::display_callback',
				'WP_Widget_Text::widget',
				'wp-includes',
				'core',
				'widgets/class-wp-widget-text.php',
				ReflectionMethod::class,
			],
			'core_admin'                          => [
				'wp_list_widgets',
				'wp_list_widgets',
				'wp-admin',
				'core',
				'includes/widgets.php',
				ReflectionFunction::class,
			],
			'plugin_closure'                      => [
				function () {},
				__NAMESPACE__ . '\{closure}',
				'amp',
				'plugin',
				'tests/php/src/DevTools/CallbackReflectionTest.php',
				ReflectionFunction::class,
			],
		];
	}

	/**
	 * Test get_source().
	 *
	 * @dataProvider data_get_source
	 * @covers ::get_source()
	 * @covers ::get_reflection()
	 *
	 * @param string                           $function         Function.
	 * @param string                           $source_function  Source function identified.
	 * @param string                           $name             Name.
	 * @param string                           $type             Type.
	 * @param string                           $file             File.
	 * @param string|\PHPUnit\Framework\string $reflection_class Reflection class.
	 */
	public function test_get_source( $function, $source_function, $name, $type, $file, $reflection_class ) {
		$source = $this->callback_reflection->get_source( $function );
		$this->assertEquals( $type, $source['type'] );
		$this->assertEquals( $name, $source['name'] );
		$this->assertEquals( $file, $source['file'] );
		$this->assertTrue( is_int( $source['line'] ) );
		$this->assertEquals( $source_function, $source['function'] );
		/** @var ReflectionFunction $reflection */
		$reflection = $source['reflection'];
		$this->assertInstanceOf( $reflection_class, $reflection );
		$this->assertEquals( preg_replace( '/.*::/', '', $source['function'] ), $reflection->getName() );
	}
}
