<?php
/**
 * Widget chat visiteur ↔ admin (flux type Wix).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

if ( ! afvf_site_chat_enabled() ) {
	return;
}
?>
<div class="site-chat" id="site-chat" hidden>
	<div class="site-chat__panel" role="dialog" aria-labelledby="site-chat-title" aria-modal="true">
		<header class="site-chat__head">
			<div class="site-chat__head-text">
				<h2 id="site-chat-title" class="site-chat__title"><?php esc_html_e( 'AFVF', 'afvf' ); ?></h2>
				<p class="site-chat__status"><span class="site-chat__status-dot" aria-hidden="true"></span> <span data-chat-online></span></p>
			</div>
			<button type="button" class="site-chat__close" data-chat-close aria-label="<?php esc_attr_e( 'Fermer', 'afvf' ); ?>">&times;</button>
		</header>
		<div class="site-chat__thread" data-chat-thread>
			<div class="site-chat__messages" data-chat-messages role="log" aria-live="polite"></div>
			<div class="site-chat__contact" data-chat-contact hidden>
				<p class="site-chat__contact-prompt" data-chat-contact-prompt></p>
				<form data-chat-contact-form>
					<label class="site-chat__label"><span data-chat-label-name></span>
						<input type="text" name="name" required autocomplete="name">
					</label>
					<label class="site-chat__label"><span data-chat-label-email></span>
						<input type="email" name="email" required autocomplete="email">
					</label>
					<label class="site-chat__label"><span data-chat-label-message></span>
						<input type="text" name="message" autocomplete="off">
					</label>
					<button type="submit" class="site-chat__ok" data-chat-label-ok></button>
				</form>
			</div>
		</div>
		<form class="site-chat__compose" data-chat-compose>
			<div class="site-chat__compose-inner">
				<textarea name="message" rows="1" required data-chat-input aria-label="<?php esc_attr_e( 'Message', 'afvf' ); ?>" placeholder="<?php esc_attr_e( 'Rédigez votre message…', 'afvf' ); ?>"></textarea>
				<div class="site-chat__compose-tools">
					<button type="button" class="site-chat__tool site-chat__tool--emoji" data-chat-emoji aria-label="<?php esc_attr_e( 'Emoji', 'afvf' ); ?>" aria-expanded="false" aria-controls="site-chat-emoji">
						<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><circle cx="9" cy="10" r="1" fill="currentColor" stroke="none"/><circle cx="15" cy="10" r="1" fill="currentColor" stroke="none"/></svg>
					</button>
					<button type="button" class="site-chat__tool site-chat__tool--attach" data-chat-attach aria-label="<?php esc_attr_e( 'Joindre un fichier', 'afvf' ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
					</button>
				</div>
			</div>
			<input type="file" class="site-chat__file-input" data-chat-file hidden accept="image/jpeg,image/png,image/gif,image/webp,application/pdf">
			<div class="site-chat__emoji-panel" id="site-chat-emoji" data-chat-emoji-panel hidden>
				<button type="button" data-emoji="😊">😊</button>
				<button type="button" data-emoji="👍">👍</button>
				<button type="button" data-emoji="❤️">❤️</button>
				<button type="button" data-emoji="🙏">🙏</button>
				<button type="button" data-emoji="😂">😂</button>
				<button type="button" data-emoji="✨">✨</button>
			</div>
		</form>
		<p class="site-chat__error" data-chat-error hidden></p>
	</div>
</div>
<button type="button" class="footer-chat-fab site-chat-fab" data-chat-open aria-expanded="false" aria-controls="site-chat"></button>
