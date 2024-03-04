<?php

namespace Changwoo\ShoplicSeminar\Supports;

use function Changwoo\ShoplicSeminar\render;

class Notice1Support implements FrontSupport {
	/**
	 * 프론트 출력
	 *
	 * @param string $prefix
	 * @param array  $atts
	 *
	 * @return string
	 */
	public function getFrontUI( string $prefix, array $atts ): string {
		// jQuery 로딩
		wp_enqueue_script( 'jquery' );
		wp_localize_script(
			'jquery',
			'notice_1',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'seminar' ),
			]
		);

		return render(
			'notice-1',
			[
				'prefix' => $prefix,
				...$atts,
			],
			true
		);
	}

	/**
	 * AJAX greetings 대응
	 *
	 * @return string
	 */
	public function getGreetings(): string {
		return '안녕하세요,';
	}

	/**
	 * AJAX thankyou 대응
	 *
	 * @return string
	 */
	public function getThankyou(): string {
		return '감사합니다!';
	}
}
