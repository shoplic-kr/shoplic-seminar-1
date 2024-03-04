<?php

namespace Changwoo\ShoplicSeminar\Supports;

class BarSeed implements Support {
	public function getRandSeed(): string {
		return wp_generate_password( 6, false ) . '-';
	}
}
