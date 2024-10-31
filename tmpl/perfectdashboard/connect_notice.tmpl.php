<?php function_exists('add_action') or die; ?>
<div class="updated" style="clear: both">
    <?php include dirname(__FILE__) . '/connect_form.tmpl.php'; ?>

    <p style="margin: 25px 0 0 80px; font-size: 16px; display: inline-block;">
        <img src="<?php echo plugins_url('media/images/shield.svg', AUTOUPDATER_WP_PLUGIN_FILE); ?>" alt="Perfect Dashboard"
             style="float: left; width: 60px; margin: -10px 0 0 -70px;">
        <strong><?php _e('Well done!', 'autoupdater'); ?></strong><br>
        <?php printf(__('You are just a step away from automating updates & backups on this website with %s', 'autoupdater'), '<strong style="color:#0aa6bd">Perfect Dashboard</strong>'); ?>
    </p>

    <button type="button" class="button button-primary button-hero"
            onclick="document.getElementById('autoupdater_connect_form').submit()"
            style="margin: 25px 0 25px 20px; vertical-align: top; font-size: 18px;"><?php _e('Finish configuration', 'autoupdater'); ?></button>
</div>