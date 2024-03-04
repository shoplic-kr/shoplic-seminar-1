<?php
/**
 * Context:
 *
 * @var string $prefix
 * @var string $p1
 * @var string $p2
 */

?>

<!-- 제목은 접두어를 갖춰 출력 -->
<h1>
	<?php echo esc_html( $prefix ); ?>notice #1
</h1>

<!-- 숏코드에서 지정한 파라미터 출력 -->
<p>
    P1: <?php echo esc_html( $p1 ); ?>, P2: <?php echo esc_html( $p2 ); ?>
</p>

<!-- 1번 템플릿 특성: AJAx -->
<p>
    <span id="greetings"></span> <span id="thankyou"></span>
</p>

<script>
    // 프론트 스크립트 실행
    jQuery(function ($) {
        const obj = $.extend({
            ajax_url: '',
            nonce: '',
        }, window.notice_1)

        $.get(obj.ajax_url, {
            action: 'seminar_greetings',
            _wpnonce: obj.nonce,
        }, function (response) {
            $('#greetings').text(response)
        })

        $.get(obj.ajax_url, {
            action: 'seminar_thankyou',
            _wpnonce: obj.nonce,
        }, function (response) {
            $('#thankyou').text(response)
        })

        console.log(obj)
    })
</script>
