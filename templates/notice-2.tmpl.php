<?php
/**
 * Context:
 *
 * @var string $prefix
 * @var string $p1
 * @var string $p2
 * @var string $foo
 */

if ( ! isset( $foo ) ) {
	$foo = 'default';
}
?>

<h1>
	<?php echo esc_html( $prefix ); ?>notice #2
</h1>
<p>
    P1: <?php echo esc_html( $p1 ); ?>, P2: <?php echo esc_html( $p2 ); ?>
</p>
<p>
    Foo: <?php echo esc_html( $foo ); ?>
</p>