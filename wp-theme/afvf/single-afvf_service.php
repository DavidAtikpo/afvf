<?php
/**
 * Page d’un service — « Programmer votre service » (calendrier + réservation).
 *
 * @package AFVF
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="afvf-bk-page">
		<?php echo afvf_service_booking_html( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
endwhile;

get_footer();
