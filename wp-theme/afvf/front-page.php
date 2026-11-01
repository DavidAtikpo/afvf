<?php
/**
 * Page d'accueil — mise en page type Wix (hero + 3 blocs).
 *
 * @package AFVF
 */

get_header();

$editor_layout = afvf_use_editor_layout();
$hero_on       = ! $editor_layout && (bool) get_theme_mod( 'afvf_show_hero', true );
$member  = afvf_mod( 'afvf_member_url' );
$hashtag = afvf_mod( 'afvf_ticker', '#ARRETONSLESFEMINICIDES' );
$quote   = afvf_mod(
	'afvf_hero_quote',
	"Elles s'appelaient Sandra, Julie, Aïssatou... Pour vous c'est un fait divers, pour nous un drame à jamais... (Extrait Appel du 06 Juillet 2019)"
);

while ( have_posts() ) :
	the_post();

	if ( $hero_on ) :
		$bg = afvf_hero_background_url();
		?>
		<section class="hero-wix"<?php echo $bg ? ' style="background-image:url(' . esc_url( $bg ) . ')"' : ''; ?>>
			<div class="hero-wix__shade" aria-hidden="true"></div>
			<div class="hero-wix__body">
				<?php if ( $member ) : ?>
					<a class="hero-wix__adhere" href="<?php echo esc_url( $member ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Adhérer', 'afvf' ); ?></a>
				<?php endif; ?>
				<div class="hero-wix__brand">
					<?php afvf_the_brand_logo( 'hero-wix__logo' ); ?>
					<?php if ( $quote ) : ?>
						<p class="hero-wix__quote"><?php echo esc_html( $quote ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $hashtag ) : ?>
					<p class="hero-wix__hashtag" aria-hidden="true"><?php echo esc_html( $hashtag ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<section class="hero-tiles" aria-label="<?php esc_attr_e( 'Accès rapide', 'afvf' ); ?>">
			<?php
			$tile_defs = array(
				array( 'afvf_tile1_title', 'afvf_tile1_text', 'afvf_tile1_url', 'hero-tile--pink' ),
				array( 'afvf_tile2_title', 'afvf_tile2_text', 'afvf_tile2_url', 'hero-tile--sky' ),
				array( 'afvf_tile3_title', 'afvf_tile3_text', 'afvf_tile3_url', 'hero-tile--navy' ),
			);
			foreach ( $tile_defs as $def ) {
				list( $tkey, $xkey, $ukey, $class ) = $def;
				$title = afvf_mod( $tkey, '' );
				$text  = afvf_mod( $xkey, '' );
				$link  = afvf_mod( $ukey, '#' );
				if ( ! str_starts_with( $link, 'http' ) ) {
					$link = home_url( '/' . trim( $link, '/' ) . '/' );
				}
				if ( ! $title ) {
					continue;
				}
				printf(
					'<a class="hero-tile %s" href="%s"><strong>%s</strong><span>%s</span></a>',
					esc_attr( $class ),
					esc_url( $link ),
					esc_html( $title ),
					esc_html( $text )
				);
			}
			?>
		</section>
		<?php
	endif;
	?>
	<section class="<?php echo esc_attr( $editor_layout ? 'afvf-block-content home-content' : 'wrap prose home-content' ); ?>">
		<?php the_content(); ?>
	</section>
	<?php
endwhile;

get_footer();
