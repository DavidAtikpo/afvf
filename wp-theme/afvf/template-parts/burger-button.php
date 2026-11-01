<?php
/**
 * Bouton menu mobile (hamburger).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

$extra_class = ! empty( $args['class'] ) ? ' ' . esc_attr( $args['class'] ) : '';
?>
<button class="burger<?php echo $extra_class; ?>" type="button" aria-expanded="false" aria-controls="menu" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'afvf' ); ?>">
	<span class="burger__bars" aria-hidden="true"></span>
</button>
