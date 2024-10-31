<?php function_exists('add_action') or die; ?>
<form action="<?php echo AutoUpdater_Config::getAutoUpdaterUrl(); ?>site/connect?utm_source=backend&amp;utm_medium=installer&amp;utm_campaign=in&amp;utm_content=wp_plugin_installer"
      method="post" id="autoupdater_connect_form" target="_blank" style="margin: 0">
    <input type="hidden" name="read_token" value="<?php echo AutoUpdater_Config::get('read_token'); ?>">
    <input type="hidden" name="write_token" value="<?php echo AutoUpdater_Config::get('write_token'); ?>">
    <input type="hidden" name="aes_key" value="<?php echo AutoUpdater_Config::get('aes_key'); ?>">
    <input type="hidden" name="user_email" value="<?php echo $user_email; ?>">
    <input type="hidden" name="site_frontend_url" value="<?php echo AutoUpdater_Config::getSiteUrl(); ?>">
    <input type="hidden" name="site_backend_url" value="<?php echo AutoUpdater_Config::getSiteBackendUrl(); ?>">
    <input type="hidden" name="cms_type" value="wordpress">
    <input type="hidden" name="cms_version" value="<?php echo AUTOUPDATER_WP_VERSION; ?>">
    <input type="hidden" name="version" value="<?php echo AUTOUPDATER_VERSION; ?>">
</form>