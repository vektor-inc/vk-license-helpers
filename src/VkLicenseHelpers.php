<?php //phpcs:ignore
/**
 * Class VkLicenseHelpers
 *
 * @package vektor-inc/vk-license-helpers
 * @license GPL-2.0+
 *
 * @version 0.0.0
 */

 namespace VektorInc\VK_License_Helpers;

class VkLicenseHelpers {

	/**
	 * ベクトル製品のライセンスキーを取得して配列を作成
	 *
	 * @return array $license_data: ベクトル製品のライセンスキー配列
	 */
	public static function get_license_data() {

		$product_data = array(
			'vk-ab-testing'         => array(
				'option' => 'vk_ab_testing_license_key',
			),
			'lightning-g3-pro-unit' => array(
				'option' => 'lightning-g3-pro-unit-license-key',
			),
			'vk-blocks-pro'         => array(
				// 第一引数が option の名前、第二引数が option のキー
				'option' => array( 'vk_blocks_options', 'vk_blocks_pro_license_key' ),
			),
		);

		foreach ( $product_data as $key => $value ) {

			if ( is_string( $value['option'] ) ) {
				// $value['option'] が文字列の場合（ license 保存専用の option に格納されている場合）

				$product_data[ $key ]['license'] = get_option( $value['option'] );

			} elseif ( is_array( $value['option'] ) ) {

				// $value['option'] が配列の場合（ option が配列の中に格納されている場合 ）
				$option = get_option( $value['option'][0] );
				if ( ! empty( $option[ $value['option'][1] ] ) ) {
					$product_data[ $key ]['license'] = $option[ $value['option'][1] ];
				} else {
					$product_data[ $key ]['license'] = '';
				}
			}
		}

		return $product_data;
	}

	/**
	 * ライセンスキーを取得
	 * 未入力の場合に他の製品のライセンスキーがあれば代わりにそれを返す
	 *
	 * @param string $target_product: 取得対象の製品名
	 * @return string $return: license key
	 */
	public static function get_license_key( $target_product ) {

		$license_data = self::get_license_data();

		$return = '';
		if ( ! empty( $license_data[ $target_product ]['license'] ) ) {
			$return = $license_data[ $target_product ]['license'];
		} else {
			// ライセンスキーが未入力の場合に他の製品のライセンスキーがあればセットする
			foreach ( $license_data as $key => $value ) {
				if ( ! empty( $value['license'] ) ) {
					// option が文字列の場合はそのまま option をupdate
					if ( is_string( $license_data[ $target_product ]['option'] ) ) {

						$return = $value['license'];

						// ライセンスキー入りの option にアップデート ////////////////////////////////////
						// ...と思ったがアップデートしなくても特に問題なさそうなのでコメントアウト
						// update_option( $license_data[ $target_product ]['option'], $value['license'] );

						break;

					} elseif ( is_array( $license_data[ $target_product ]['option'] ) ) {

						// option が配列の場合

						$return = $value['license'];

						// ライセンスキー入りの option にアップデート ////////////////////////////////////
						// ...と思ったがアップデートしなくても特に問題なさそうなのでコメントアウト
						// 一旦 option を取得
						// $option = get_option( $license_data[ $target_product ]['option'][0] );
						// ライセンスキーを格納
						// $option[ $license_data[ $target_product ]['option'][1] ] = $value['license'];
						// ライセンスキー入りの option をアップデート
						// update_option( $license_data[ $target_product ]['option'][0], $option );

						break;
					}
				}
			}
		}

		return $return;
	}
}
