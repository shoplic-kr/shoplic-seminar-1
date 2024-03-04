<?php

namespace Changwoo\ShoplicSeminar\Supports;

use function Changwoo\ShoplicSeminar\render;

class Notice2Support implements FrontSupport {
	private FooGen $fg;

	/**
	 * 생성자
	 *
	 * @param FooGen $fg
	 */
	public function __construct( FooGen $fg ) {
		$this->fg = $fg;
	}

	/**
	 * 프론트 출력
	 *
	 * @param string $prefix
	 * @param array  $atts
	 *
	 * @return string
	 */
	public function getFrontUI( string $prefix, array $atts ): string {
//		$fg = new FooGen();
		$fg = $this->fg;

		$foo = $fg->getFoo();

		return render(
			'notice-2',
			[
				'prefix' => $prefix,
				'foo'    => $foo,
				...$atts,
			],
			true
		);
	}
}
