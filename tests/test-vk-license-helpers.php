<?php
/**
 * Class VkLicenseHelpers
 *
 * @package vektor-inc/vk-license-helpers
 */

use VektorInc\VK_License_Helpers\VkLicenseHelpers;

class VK_License_Helper_Test extends WP_UnitTestCase {

	public function test_get_license_data() {

		// テスト配列 .
		$test_array = array(
			// ライセンスキー未登録.
			array(
				'options' => array(
					'vk-ab-testing-license-key'         => '',
					'lightning-g3-pro-unit-license-key' => '',
					'vk_blocks_options'                 => array(
						'vk_blocks_pro_license_key' => '',
					),

				),
				'expect'  => array(
					'vk-ab-testing'         => array(
						'option'  => 'vk_ab_testing_license_key',
						'license' => '',
					),
					'lightning-g3-pro-unit' => array(
						'option'  => 'lightning-g3-pro-unit-license-key',
						'license' => '',
					),
					'vk-blocks-pro'         => array(
						'option'  => array( 'vk_blocks_options', 'vk_blocks_pro_license_key' ),
						'license' => '',
					),
				),
			),
		);

		foreach ( $test_array as $key => $value ) {
			if ( is_array( $value['options'] ) ) {
				foreach ( $value['options'] as $option_name => $option_value ) {
					if ( $option_value ) {
						update_option( $option_name, $option_value );
					}
				}
			}
			$return = VkLicenseHelpers::get_license_data();

			// print 'return : ' . PHP_EOL;
			// print '<pre style="text-align:left">';print_r($return);print '</pre>' . PHP_EOL;
			// print 'expect : ' . PHP_EOL;
			// print '<pre style="text-align:left">';print_r($value['expect']);print '</pre>';
			$this->assertEquals( $value['expect'], $return );
		}
	}

	public function get_license_key() {

		// テスト配列 .
		$test_array = array(
			array(
				'test_name'      => 'vk-ab-testing 普通に登録されている場合',
				'target_product' => 'vk-ab-testing',
				'options'        => array(
					'vk-ab-testing-license-key'         => 'ab-testing-key',
					'lightning-g3-pro-unit-license-key' => '',
					'vk_blocks_options'                 => array(
						'vk_blocks_pro_license_key' => '',
					),

				),
				'expect'         => 'ab-testing-key',
			),
			array(
				'test_name'      => 'vk-ab-testing 普通に登録されている場合（他にも登録あり）',
				'target_product' => 'vk-ab-testing',
				'options'        => array(
					'vk-ab-testing-license-key'         => 'ab-testing-key',
					'lightning-g3-pro-unit-license-key' => 'g3-key',
					'vk_blocks_options'                 => array(
						'vk_blocks_pro_license_key' => 'vk-blocks-key',
					),

				),
				'expect'         => 'ab-testing-key',
			),
			array(
				'test_name'      => 'vk-ab-testing 未登録 _ G3 Pro Unit からひっぱる',
				'target_product' => 'vk-ab-testing',
				'options'        => array(
					'lightning-g3-pro-unit-license-key' => 'g3-key',
					'vk_blocks_options'                 => array(
						'vk_blocks_pro_license_key' => '',
					),

				),
				'expect'         => 'g3-key',
			),
			array(
				'test_name'      => 'vk-ab-testing 未登録 _ VK Blocks Pro からひっぱる',
				'target_product' => 'vk-ab-testing',
				'options'        => array(
					'lightning-g3-pro-unit-license-key' => '',
					'vk_blocks_options'                 => array(
						'vk_blocks_pro_license_key' => 'vk-blocks-key',
					),

				),
				'expect'         => 'vk-blocks-key',
			),

		);

		foreach ( $test_array as $key => $value ) {
			if ( is_array( $value['options'] ) ) {
				foreach ( $value['options'] as $option_name => $option_value ) {
					if ( $option_value ) {
						update_option( $option_name, $option_value );
					}
				}
			}
			$return = VkLicenseHelpers::get_license_key( $value['target_product'] );

			// print 'return : ' . PHP_EOL;
			// print '<pre style="text-align:left">';print_r($return);print '</pre>' . PHP_EOL;
			// print 'expect : ' . PHP_EOL;
			// print '<pre style="text-align:left">';print_r($value['expect']);print '</pre>';
			$this->assertEquals( $value['expect'], $return );

		}
	}
}
