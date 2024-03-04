<?php

namespace Changwoo\ShoplicSeminar\Modules;

use Changwoo\ShoplicSeminar\Supports\Notice1Support;

use function Changwoo\ShoplicSeminar\toPascalCase;

class Ajax implements Module {
	public function __construct() {
		/**
		 * @uses handleGreetings
		 * @uses handleThankyou
		 */
		$this->addAjax( 'seminar_greetings' );
		$this->addAjax( 'seminar_thankyou' );
	}

	public function dispatch(): never {
		check_ajax_referer( 'seminar' );

		$action = sanitize_key( wp_unslash( $_REQUEST['action'] ?? '' ) );
		$method = str_replace( '-', '_', $action );
		if ( str_starts_with( $method, 'seminar_' ) ) {
			$method = substr( $method, 8 );
		}

		$method = 'handle' . toPascalCase( $method );

		// Dynamic method call.
		if ( method_exists( $this, $method ) ) {
			$this->$method();
		}

		exit;
	}

	private function handleGreetings(): never {
		echo seminar()->get(Notice1Support::class)->getGreetings();
		exit;
	}

	private function handleThankyou(): never {
		echo seminar()->get(Notice1Support::class)->getThankyou();
		exit;
	}

	/**
	 * 예시를 위해 경량화한 AJAX 추가 메소드.
	 *
	 * @param string $handle
	 *
	 * @return void
	 */
	private function addAjax( string $handle ): void {
		add_action( "wp_ajax_$handle", [ $this, 'dispatch' ] );
	}
}