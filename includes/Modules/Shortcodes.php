<?php

namespace Changwoo\ShoplicSeminar\Modules;

use Changwoo\ShoplicSeminar\Supports\Notice1Support;
use Changwoo\ShoplicSeminar\Supports\Notice2Support;

use function Changwoo\ShoplicSeminar\toPascalCase;

/**
 * 모든 숏코드의 등록과 처리를 담당하는 모듈.
 */
class Shortcodes implements Module {
	private string $prefix;

	public function __construct( string $prefix ) {
		$this->prefix = $prefix;

		/**
		 * @uses handleNotice1
		 * @uses handleNotice2
		 */
		$this->addShortcode( 'seminar_notice_1' );
		$this->addShortcode( 'seminar_notice_2' );
	}

	/**
	 * 숏코드 대표 핸들러
	 *
	 * @param array|string $atts
	 * @param string       $enclosed
	 * @param string       $shortcode
	 *
	 * @return string
	 */
	public function dispatch( array|string $atts, string $enclosed, string $shortcode ): string {
		$output = '';

		if ( is_admin() ) {
			return "&lbrack;$shortcode&rbrack;";
		}

		// Shortcode string to method name.
		$method = $shortcode;
		if ( str_starts_with( $method, 'seminar_' ) ) {
			$method = substr( $method, 8 );
		}

		$method = 'handle' . toPascalCase( $method );

		// Dynamic method call.
		if ( method_exists( $this, $method ) ) {
			$output = $this->$method( $atts, $enclosed, $shortcode );
		}

		return $output;
	}

	private function handleNotice1( array|string $atts ): string {
		$pairs = [
			'p1' => '#1',
			'p2' => 'module',
		];

		$atts = shortcode_atts( $pairs, $atts );

		return seminar()->get( Notice1Support::class )->getFrontUI( $this->prefix, $atts );
	}

	private function handleNotice2( array|string $atts ): string {
		$pairs = [
			'p1' => '#2',
			'p2' => 'seminar',
		];

		$atts = shortcode_atts( $pairs, $atts );

		return seminar()->get( Notice2Support::class )->getFrontUI( $this->prefix, $atts );
	}

	/**
	 * 'dispatch()' 메소드를 사용한 숏코드를 등록.
	 *
	 * @param string $shortcode
	 *
	 * @return void
	 */
	private function addShortcode( string $shortcode ): void {
		add_shortcode( $shortcode, [ $this, 'dispatch' ] );
	}
}
