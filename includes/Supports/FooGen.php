<?php

namespace Changwoo\ShoplicSeminar\Supports;

class FooGen implements Support {
	private string $prefix;

	public function __construct(BarSeed $seed) {
		$this->prefix = $seed->getRandSeed();
	}

	public function getFoo(): string {
		return $this->prefix . 'foo';
	}
}
