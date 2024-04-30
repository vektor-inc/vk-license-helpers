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

	// コンストラクタ
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_custom_admin_style' ) );
	}

	/**
	 * 管理画面スタイルの追加
	 */
	public static function add_custom_admin_style() {
		// notice用のスタイル
		$custom_css = '.nowrap { white-space: nowrap; }';
		// 'wp-admin' スタイルシートにインラインスタイルを追加
		wp_add_inline_style( 'wp-admin', $custom_css );
	}

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

	/**
	 * 管理画面アラートメッセージの生成
	 * ※ 外側は将来的に wp_admin_notice() 関数などに変更するかもしれないので、中身のみを関数化
	 * https://www.vektor-inc.co.jp/post/wordpress-6-4-wp_admin_notice/
	 *
	 * @param string $args : ライセンス情報
	 * @return string $notice : 生成されたアラートメッセージ
	 */
	public static function get_license_notice_body( $args = array() ) {

		$args_default = array(
			'product_name'    => '',
			'status'          => '',
			'register_url'    => '',
			'purchase_url'    => '',
			'additional_html' => '',
		);

		$args = wp_parse_args( $args, $args_default );

		$notice = '';

		if ( 'unregistered' === $args['status'] || 'expired' === $args['status'] ) {

			$notice .= '<h4>' . $args['product_name'] . '</h4>';
			$notice .= '<>';
			if ( 'expired' === $args['status'] ) {
				// 期限が切れている場合.
				$notice .= __( 'Your license key is expired.', 'vk-license-helpers' );
			}
			if ( 'unregistered' === $args['status'] ) {
				// ライセンスキーが未入力の場合.
				$notice .= __( 'License Key has no registered.', 'vk-license-helpers' );
			}
			$notice .= ' ';
			$notice .= __( 'Please register a valid license key.', 'vk-license-helpers' );
			$notice .= '</p>';

			if ( ! empty( $args['additional_html'] ) ) {
				$notice .= $args['additional_html'];
			}

			$notice .= '<a href="' . esc_url( $args['register_url'] ) . '" class="button button-primary">' . __( 'Register license key', 'vk-license-helpers' ) . '</a>';
			if ( ! empty( $args['purchase_url'] ) ) {
				$notice .= ' <a href="' . esc_url( $args['purchase_url'] ) . '" class="button button-secondary">' . __( 'Purchase a license', 'vk-license-helpers' ) . '</a>';
			}
			$notice .= '</p>';

			/* translators: %s: 再読み込みURL */
			$notice .= __( 'If this display does not disappear even after entering a valid license key, re-acquire the update.', 'lightning-g3-pro-unit' );
			$notice .= '<span class="nowrap">[ <a href="' . admin_url( '/' ) . 'update-core.php?force-check=1' . '">' . __( 'Re-acquisition of updates', 'vk-license-helpers' ) . '</a> ]</span>';
			$notice .= '</p>';

		}

		return $notice;
	}
}
