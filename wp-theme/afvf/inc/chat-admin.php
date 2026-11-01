<?php
/**
 * Admin — boîte de réception (chat + abonnements), vue 2 colonnes.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_chat_admin_menu() {
	$unread = afvf_chat_count_unread();
	$title  = __( 'Boîte de réception', 'afvf' );
	if ( $unread > 0 ) {
		$title .= sprintf( ' <span class="awaiting-mod count-%1$d"><span class="pending-count">%1$d</span></span>', $unread );
	}
	add_menu_page(
		__( 'Boîte de réception AFVF', 'afvf' ),
		$title,
		'manage_options',
		'afvf-inbox',
		'afvf_inbox_admin_page',
		'dashicons-email',
		57
	);
}
add_action( 'admin_menu', 'afvf_chat_admin_menu' );

/**
 * @return array{thread_id: int, sub_id: int}
 */
function afvf_inbox_resolve_selection() {
	$thread_id = isset( $_GET['thread_id'] ) ? (int) $_GET['thread_id'] : 0;
	$sub_id    = isset( $_GET['sub_id'] ) ? (int) $_GET['sub_id'] : 0;
	if ( $thread_id || $sub_id ) {
		return array(
			'thread_id' => $thread_id,
			'sub_id'    => $sub_id,
		);
	}
	$items = afvf_inbox_collect_items();
	if ( empty( $items ) ) {
		return array(
			'thread_id' => 0,
			'sub_id'    => 0,
		);
	}
	if ( 'chat' === $items[0]['type'] ) {
		return array(
			'thread_id' => (int) $items[0]['id'],
			'sub_id'    => 0,
		);
	}
	return array(
		'thread_id' => 0,
		'sub_id'    => (int) $items[0]['id'],
	);
}

function afvf_inbox_admin_assets( $hook ) {
	if ( 'toplevel_page_afvf-inbox' !== $hook ) {
		return;
	}
	wp_enqueue_style(
		'afvf-inbox-admin',
		get_template_directory_uri() . '/assets/css/inbox-admin.css',
		array(),
		'1.7.0'
	);
	$selection = afvf_inbox_resolve_selection();
	$thread_id = $selection['thread_id'];
	if ( $thread_id ) {
		wp_enqueue_script(
			'afvf-inbox-admin',
			get_template_directory_uri() . '/assets/js/inbox-admin.js',
			array(),
			'1.7.0',
			true
		);
		wp_localize_script(
			'afvf-inbox-admin',
			'afvfInbox',
			array(
				'restHome' => home_url( '/' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'pollMs'   => 2000,
				'threadId' => $thread_id,
				'strings'  => array(
					'formSent' => __( 'FORMULAIRE ENVOYÉ PAR CHAT', 'afvf' ),
				),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'afvf_inbox_admin_assets' );

function afvf_chat_count_unread() {
	global $wpdb;
	return (int) $wpdb->get_var( 'SELECT COALESCE(SUM(admin_unread), 0) FROM ' . afvf_chat_threads_table() . " WHERE status = 'open'" );
}

/**
 * @return array<int, array<string, mixed>>
 */
function afvf_inbox_collect_items() {
	global $wpdb;
	$items = array();

	$threads = $wpdb->get_results( 'SELECT * FROM ' . afvf_chat_threads_table() . ' ORDER BY updated_at DESC LIMIT 80' );
	foreach ( $threads as $t ) {
		$preview = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT body FROM ' . afvf_chat_messages_table() . ' WHERE thread_id = %d ORDER BY id DESC LIMIT 1',
				(int) $t->id
			)
		);
		$items[] = array(
			'type'    => 'chat',
			'id'      => (int) $t->id,
			'label'   => afvf_chat_visitor_label( $t ),
			'preview' => $preview ? wp_trim_words( $preview, 12, '…' ) : '',
			'date'    => $t->updated_at,
			'unread'  => (int) $t->admin_unread,
		);
	}

	if ( function_exists( 'afvf_newsletter_table' ) ) {
		$subs = $wpdb->get_results(
			'SELECT id, email, status, subscribed_at FROM ' . afvf_newsletter_table() . " WHERE status IN ('active','pending') ORDER BY subscribed_at DESC LIMIT 80"
		);
		foreach ( $subs as $s ) {
			$items[] = array(
				'type'    => 'subscription',
				'id'      => (int) $s->id,
				'label'   => $s->email,
				'preview' => __( 'Abonnement — formulaire pied de page', 'afvf' ),
				'date'    => $s->subscribed_at,
				'unread'  => 0,
			);
		}
	}

	usort(
		$items,
		static function ( $a, $b ) {
			return strcmp( $b['date'], $a['date'] );
		}
	);

	return array_slice( $items, 0, 100 );
}

function afvf_chat_admin_handle_reply() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['afvf_chat_reply_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_chat_reply_nonce'] ) ), 'afvf_chat_reply' ) ) {
		return;
	}

	$thread_id = isset( $_POST['thread_id'] ) ? (int) $_POST['thread_id'] : 0;
	$body      = isset( $_POST['reply_body'] ) ? wp_strip_all_tags( wp_unslash( $_POST['reply_body'] ) ) : '';
	$notify    = ! empty( $_POST['notify_email'] );

	global $wpdb;
	$thread = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . afvf_chat_threads_table() . ' WHERE id = %d', $thread_id ) );
	if ( ! $thread || '' === trim( $body ) ) {
		return;
	}

	afvf_chat_add_message( $thread_id, 'admin', $body );
	$wpdb->update(
		afvf_chat_threads_table(),
		array( 'admin_unread' => 0 ),
		array( 'id' => $thread_id ),
		array( '%d' ),
		array( '%d' )
	);

	if ( $notify ) {
		afvf_chat_notify_visitor( $thread, $body );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=afvf-inbox&thread_id=' . $thread_id . '&sent=1' ) );
	exit;
}
add_action( 'admin_init', 'afvf_chat_admin_handle_reply' );

function afvf_inbox_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$selection = afvf_inbox_resolve_selection();
	$thread_id = $selection['thread_id'];
	$sub_id    = $selection['sub_id'];
	$items     = afvf_inbox_collect_items();
	?>
	<div class="wrap afvf-inbox-wrap">
		<h1><?php esc_html_e( 'Boîte de réception', 'afvf' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Liste à gauche, conversation à droite — mise à jour automatique toutes les 2 secondes.', 'afvf' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=afvf-newsletter-broadcast' ) ); ?>"><?php esc_html_e( 'Campagne newsletter', 'afvf' ); ?></a>
		</p>
		<div class="afvf-inbox-layout">
			<aside class="afvf-inbox-sidebar">
				<div class="afvf-inbox-sidebar__head"><?php esc_html_e( 'Toutes les conversations', 'afvf' ); ?></div>
				<?php if ( empty( $items ) ) : ?>
					<p style="padding:1rem;color:#646970;"><?php esc_html_e( 'Aucun message.', 'afvf' ); ?></p>
				<?php else : ?>
					<?php foreach ( $items as $item ) : ?>
						<?php
						if ( 'chat' === $item['type'] ) {
							$url    = admin_url( 'admin.php?page=afvf-inbox&thread_id=' . $item['id'] );
							$active = (int) $thread_id === (int) $item['id'];
							$type   = __( 'Chat', 'afvf' );
						} else {
							$url    = admin_url( 'admin.php?page=afvf-inbox&sub_id=' . $item['id'] );
							$active = (int) $sub_id === (int) $item['id'];
							$type   = __( 'Abonnement', 'afvf' );
						}
						?>
						<a class="afvf-inbox-item<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
							<?php
							echo wp_kses_post(
								afvf_contact_avatar_markup(
									$item['label'],
									array(
										'size'            => 'md',
										'show_online'     => ( 'chat' === $item['type'] && ! empty( $item['unread'] ) ),
										'show_chat_badge' => ( 'chat' === $item['type'] ),
									)
								)
							);
							?>
							<span class="afvf-inbox-item__body">
								<span class="afvf-inbox-item__type"><?php echo esc_html( $type ); ?></span>
								<span class="afvf-inbox-item__label">
									<?php echo esc_html( $item['label'] ); ?>
									<?php if ( ! empty( $item['unread'] ) ) : ?>
										<span class="afvf-inbox-item__badge"><?php echo (int) $item['unread']; ?></span>
									<?php endif; ?>
								</span>
								<span class="afvf-inbox-item__preview"><?php echo esc_html( $item['preview'] ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</aside>
			<div class="afvf-inbox-main">
				<?php
				if ( $thread_id ) {
					afvf_inbox_render_thread_panel( $thread_id );
				} elseif ( $sub_id ) {
					afvf_inbox_render_subscription_panel( $sub_id );
				} else {
					?>
					<div class="afvf-inbox-empty"><?php esc_html_e( 'Sélectionnez une conversation.', 'afvf' ); ?></div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * @param int $thread_id Thread ID.
 */
function afvf_inbox_render_thread_panel( $thread_id ) {
	global $wpdb;
	$thread = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . afvf_chat_threads_table() . ' WHERE id = %d', $thread_id ) );
	if ( ! $thread ) {
		echo '<div class="afvf-inbox-empty">' . esc_html__( 'Conversation introuvable.', 'afvf' ) . '</div>';
		return;
	}

	$wpdb->update(
		afvf_chat_threads_table(),
		array( 'admin_unread' => 0 ),
		array( 'id' => $thread_id ),
		array( '%d' ),
		array( '%d' )
	);

	$messages = afvf_chat_get_messages( $thread_id );
	?>
	<?php $head_label = afvf_chat_visitor_label( $thread ); ?>
	<header class="afvf-inbox-main__head afvf-inbox-main__head--profile">
		<?php
		echo wp_kses_post(
			afvf_contact_avatar_markup(
				$head_label,
				array(
					'size'            => 'lg',
					'show_online'     => true,
					'show_chat_badge' => true,
				)
			)
		);
		?>
		<div class="afvf-inbox-main__head-text">
		<h2><?php echo esc_html( $head_label ); ?></h2>
		<p class="afvf-inbox-main__meta">
			<?php esc_html_e( 'Message via : Chat', 'afvf' ); ?>
			<?php if ( $thread->visitor_email ) : ?>
				· <?php echo esc_html( $thread->visitor_email ); ?>
			<?php endif; ?>
		</p>
		</div>
	</header>
	<div id="afvf-inbox-messages" class="afvf-inbox-messages">
		<?php afvf_inbox_render_messages_html( $messages ); ?>
	</div>
	<div class="afvf-inbox-compose">
		<form id="afvf-inbox-reply-form" method="post" action="">
			<?php wp_nonce_field( 'afvf_chat_reply', 'afvf_chat_reply_nonce' ); ?>
			<input type="hidden" name="thread_id" value="<?php echo (int) $thread_id; ?>">
			<textarea name="reply_body" rows="3" placeholder="<?php esc_attr_e( 'Écrivez votre message…', 'afvf' ); ?>"></textarea>
			<p>
				<label><input type="checkbox" name="notify_email" value="1" <?php checked( ! empty( $thread->visitor_email ) ); ?>> <?php esc_html_e( 'Envoyer aussi par e-mail', 'afvf' ); ?></label>
			</p>
			<?php submit_button( __( 'Envoyer', 'afvf' ), 'primary', 'submit', false ); ?>
		</form>
		<noscript><p class="description"><?php esc_html_e( 'Activez JavaScript pour l’envoi instantané et le rafraîchissement.', 'afvf' ); ?></p></noscript>
	</div>
	<?php
}

/**
 * @param array<int, object> $messages Messages.
 */
function afvf_inbox_render_messages_html( $messages ) {
	foreach ( $messages as $m ) {
		if ( 'form' === $m->sender ) {
			?>
			<div class="afvf-inbox-msg__form">
				<p class="afvf-inbox-msg__form-title"><?php esc_html_e( 'FORMULAIRE ENVOYÉ PAR CHAT', 'afvf' ); ?></p>
				<pre><?php echo esc_html( $m->body ); ?></pre>
			</div>
			<?php
			continue;
		}
		$class = 'admin' === $m->sender ? 'admin' : 'visitor';
		?>
		<div class="afvf-inbox-msg afvf-inbox-msg--<?php echo esc_attr( $class ); ?>">
			<div class="afvf-inbox-msg__bubble"><?php afvf_chat_echo_message_content( $m ); ?></div>
		</div>
		<?php
	}
}

/**
 * @param int $sub_id Subscriber ID.
 */
function afvf_inbox_render_subscription_panel( $sub_id ) {
	global $wpdb;
	$row = $wpdb->get_row(
		$wpdb->prepare(
			'SELECT * FROM ' . afvf_newsletter_table() . ' WHERE id = %d',
			$sub_id
		)
	);
	if ( ! $row ) {
		echo '<div class="afvf-inbox-empty">' . esc_html__( 'Abonné introuvable.', 'afvf' ) . '</div>';
		return;
	}
	$write = admin_url( 'admin.php?page=afvf-newsletter-write&email=' . rawurlencode( $row->email ) );
	?>
	<header class="afvf-inbox-main__head afvf-inbox-main__head--profile">
		<?php
		echo wp_kses_post(
			afvf_contact_avatar_markup(
				$row->email,
				array(
					'size'            => 'lg',
					'show_online'     => false,
					'show_chat_badge' => false,
				)
			)
		);
		?>
		<div class="afvf-inbox-main__head-text">
			<h2><?php echo esc_html( $row->email ); ?></h2>
			<p class="afvf-inbox-main__meta"><?php esc_html_e( 'Abonnement — liste de diffusion', 'afvf' ); ?></p>
		</div>
	</header>
	<div class="afvf-inbox-sub">
		<div class="afvf-inbox-sub__card">
			<p style="letter-spacing:0.08em;font-size:0.75rem;color:#666;"><?php esc_html_e( "S'EST ABONNÉ À VOTRE LISTE DE DIFFUSION", 'afvf' ); ?></p>
			<p><strong><?php esc_html_e( 'E-mail', 'afvf' ); ?>:</strong> <?php echo esc_html( $row->email ); ?></p>
			<p class="description"><?php esc_html_e( 'Formulaire automatisé (pied de page)', 'afvf' ); ?></p>
			<p><?php esc_html_e( 'Inscription :', 'afvf' ); ?> <?php echo esc_html( $row->subscribed_at ); ?></p>
		</div>
		<p style="text-align:center;">
			<a class="button button-primary" href="<?php echo esc_url( $write ); ?>"><?php esc_html_e( 'Écrire un e-mail', 'afvf' ); ?></a>
		</p>
	</div>
	<?php
}
