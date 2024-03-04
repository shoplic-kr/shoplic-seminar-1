<?php

namespace Changwoo\ShoplicSeminar\Supports;

interface FrontSupport extends Support
{
    public function getFrontUI(string $prefix, array $atts): string;
}
