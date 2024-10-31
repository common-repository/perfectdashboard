<?php function_exists('add_action') or die; ?>
<div id="autoupdater-page">
    <?php if (!$site_white_labelled) : ?>
        <div class="autoupdater-header <?php if (!$site_connected) echo 'autoupdater-center'; ?>">
            <h1 class="autoupdater-heading">
                <?php _e('Perfect Dashboard', 'autoupdater'); ?>
            </h1>
        </div>

        <div class="autoupdater <?php if (!$site_connected) echo 'autoupdater-center'; ?> autoupdater-clearfix ">

            <div class="autoupdater-start">
                <?php if (!$site_connected) : ?>
                    <h2>
                        <?php _e('Let Perfect Dashboard do all the backups & updates for you ', 'autoupdater'); ?>
                        <span><?php _e('[for&nbsp;FREE]', 'autoupdater'); ?></span>
                    </h2>

                    <ul class="autoupdater-list-features">
                        <li>
                            <span class="dashicons dashicons-yes"></span> <?php _e('The One Place You Will Ever Need to Manage All Websites Efficiently',
                                'autoupdater'); ?></li>
                        <li><span
                                    class="dashicons dashicons-yes"></span> <?php _e('Test and Validate Websites After Every Update Automatically',
                                'autoupdater'); ?></li>
                        <li><span
                                    class="dashicons dashicons-yes"></span> <?php _e('Verify Backups Automatically',
                                'autoupdater'); ?></li>
                    </ul>
                <?php endif; ?>

                <button type="button" onclick="document.getElementById('autoupdater_connect_form').submit()"
                        class="button button-primary autoupdater-big-btn">
                    <?php if ($site_connected) : ?>
                        <?php _e('Connect your website again', 'autoupdater') ?>
                    <?php else : ?>
                        <?php _e('Connect your website', 'autoupdater') ?>
                    <?php endif; ?>
                </button>
                <?php _e('to Perfect Dashboard', 'autoupdater'); ?>
            </div>

            <?php include dirname(__FILE__) . '/connect_form.tmpl.php'; ?>
        </div>
    <?php endif; ?>

    <div class="autoupdater-clearfix">
        <div class="autoupdater-template-wrapper">
            <?php echo $template_active; ?>
        </div>
        <?php if (!empty($template_inactive)) : ?>
            <div class="autoupdater-template-wrapper" style="display: none;">
                <?php echo $template_inactive; ?>
            </div>
        <?php endif; ?>
        <div id="autoupdater-form-wrapper">
            <?php include dirname(dirname(__FILE__)) . '/configuration_form.tmpl.php'; ?>
        </div>
    </div>
</div>