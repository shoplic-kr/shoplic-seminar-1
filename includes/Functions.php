<?php

namespace {

	use Changwoo\ShoplicSeminar\Container;

	function seminar(): Changwoo\ShoplicSeminar\Container {
		return Container::getInstance();
	}
}


namespace Changwoo\ShoplicSeminar {

	/**
	 * Render template file.
	 *
	 * @param string $__tmplName__
	 * @param array  $__context__
	 * @param bool   $__return__
	 *
	 * @return string
	 */
	function render( string $__tmplName__, array $__context__ = [], bool $__return__ = false ): string {
		$__output__   = '';
		$__tmplName__ = trim( $__tmplName__, '\\/' );
		$__path__     = dirname( SEMINAR_MAIN ) . "/templates/$__tmplName__.tmpl.php";

		if ( file_exists( $__path__ ) && is_file( $__path__ ) && is_readable( $__path__ ) ) {
			if ( $__return__ ) {
				ob_start();
			}

			( function ( $__path__, $__context__ ) {
				if ( $__context__ ) {
					extract( $__context__, EXTR_SKIP );
				}
				unset( $__context__ );
				include $__path__;
			} )(
				$__path__,
				$__context__
			);

			if ( $__return__ ) {
				$__output__ = ob_get_clean();
			}
		}

		return $__output__;
	}

	/**
	 * 문자열 표기법을 파스칼 케이스로 변경.
	 *
	 * this_is_a_pascal_cased_sentence ==> ThisIsAPascalCasedSentence
	 *
	 * @param string $string 입력 단어.
	 * @param string $glue   띄어쓰기 문자. 기본은 언더바 '_'
	 *
	 * @return string
	 */
	function toPascalCase(string $string, string $glue = '_'): string
	{
		return str_replace($glue, '', ucwords($string, $glue));
	}
}