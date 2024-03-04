<?php
/**
 * Plugin Name:  쇼플릭 세미나 #1 샘플 코드
 * Plugin URI:   https://github.com/chwnam/shoplic-seminar
 * Description:  20240229 쇼플릭 오픈 세미나 #1 - 모듈화된 워드프레스 플러그인 개발방법
 * Author:       changwoo
 * Author URI:   https://blog.changwoo.pe.kr
 * Requires PHP: 7.4
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Version:      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

const SEMINAR_MAIN    = __FILE__;
const SEMINAR_VERSION = '0.0.0';

seminar();
