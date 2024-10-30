<div class="wrap">
    <?= do_action('i4a_logo'); ?>

    <h2><?= do_action('i4a_head'); ?></h2>

    <h2 class='nav-tab-wrapper'>
        <?= do_action('i4a_tabs'); ?>
    </h2>

    <form method="post" action="">
        <?= do_action('i4a_body'); ?>
        <?php wp_nonce_field(); ?>
    </form>

    <?= do_action('i4a_foot'); ?>

</div>