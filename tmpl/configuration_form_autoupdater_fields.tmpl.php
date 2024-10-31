<table class="form-table">
    <tbody>
    <tr>
        <th scope="row">
            <label>
                <?php _e('Enable automatic updates', 'autoupdater') ?>
            </label>
        </th>
        <td>
            <label>
                <input type="radio" name="autoupdater_enabled" value="0" <?php if (!$autoupdater_enabled) echo 'checked="checked"'; ?>>
                <?php _e('No') ?>
            </label>
            <label>
                <input type="radio" name="autoupdater_enabled" value="1" <?php if ($autoupdater_enabled) echo 'checked="checked"'; ?>>
                <?php _e('Yes') ?>
            </label>
            <p class="description">
                <?php _e('This website will be automatically updated', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>
                <?php _e('Update WordPress core', 'autoupdater') ?>
            </label>
        </th>
        <td>
            <label>
                <input type="radio" name="update_cms" value="0" <?php if (!$update_cms) echo 'checked="checked"'; ?>>
                <?php _e('No') ?>
            </label>
            <label>
                <input type="radio" name="update_cms" value="1" <?php if ($update_cms) echo 'checked="checked"'; ?>>
                <?php _e('Yes') ?>
            </label>
            <p class="description">
                <?php _e('Enable the automatic updates of the WordPress core', 'autoupdater') ?>
                <?php
                if ($update_cms_stage === 'newest') :
                    _e('to the latest available version', 'autoupdater');
                elseif ($update_cms_stage === 'newest_with_weekly_delay') :
                    _e('to the latest available version but with a weekly delay to wait for extensions\' updates', 'autoupdater');
                else :
                    _e('to the latest stable version, excluding the release X.Y.0', 'autoupdater');
                endif;
                ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>
                <?php _e('Update plugins', 'autoupdater') ?>
            </label>
        </th>
        <td class="autoupdater-toggle-input" data-toggle-target="#autoupdater_excluded_extensions">
            <label>
                <input type="radio" name="update_extensions" value="0" <?php if (!$update_extensions) echo 'checked="checked"'; ?>>
                <?php _e('No') ?>
            </label>
            <label>
                <input type="radio" name="update_extensions" value="1" <?php if ($update_extensions) echo 'checked="checked"'; ?>>
                <?php _e('Yes') ?>
            </label>
            <p class="description">
                <?php _e('Enable automatic updates of plugins', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr id="autoupdater_excluded_extensions" <?php if (!$update_extensions) echo 'style="display: none;"'; ?>>
        <th scope="row">
            <label>
                <?php _e('Exclude selected plugins', 'autoupdater') ?>
            </label>
        </th>
        <td>
            <div class="autoupdater-toggle">
                <?php if ($extensions_list_count > 10) : ?>
                    <a href="#" class="autoupdater-toggle-button">
                        <span class="autoupdater-toggle-indicator"><?php _e('Show', 'autoupdater'); ?></span>
                        <span class="autoupdater-toggle-indicator" style="display: none;"><?php _e('Hide', 'autoupdater'); ?></span>
                    </a>
                <?php endif; ?>
                <div class="autoupdater-toggle-content" <?php if ($extensions_list_count > 10) echo 'style="display: none;"'; ?>>
                    <?php foreach ($extensions_list as $slug => $item) : ?>
                        <br>
                        <label for="excluded_extensions_<?php echo $slug; ?>" class="checkbox">
                            <input type="checkbox" id="excluded_extensions_<?php echo $slug; ?>"
                                   name="excluded_extensions[]" value="plugin::<?php echo $slug; ?>"
                                <?php if (!empty($item['excluded'])) echo 'checked="checked"'; ?>
                            >
                            <?php echo $item['name'] . ' (' . $slug . ')'; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>
                <?php _e('Update themes', 'autoupdater') ?>
            </label>
        </th>
        <td class="autoupdater-toggle-input" data-toggle-target="#autoupdater_excluded_themes">
            <label>
                <input type="radio" name="update_themes" value="0" <?php if (!$update_themes) echo 'checked="checked"'; ?>>
                <?php _e('No') ?>
            </label>
            <label>
                <input type="radio" name="update_themes" value="1" <?php if ($update_themes) echo 'checked="checked"'; ?>>
                <?php _e('Yes') ?>
            </label>
            <p class="description">
                <?php _e('Enable automatic updates of themes', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr id="autoupdater_excluded_themes" <?php if (!$update_themes) echo 'style="display: none;"'; ?>>
        <th scope="row">
            <label>
                <?php _e('Exclude selected themes', 'autoupdater') ?>
            </label>
        </th>
        <td>
            <div class="autoupdater-toggle">
                <?php if ($themes_list_count > 10) : ?>
                    <a href="#" class="autoupdater-toggle-button">
                        <span class="autoupdater-toggle-indicator"><?php _e('Show', 'autoupdater'); ?></span>
                        <span class="autoupdater-toggle-indicator" style="display: none;"><?php _e('Hide', 'autoupdater'); ?></span>
                    </a>
                <?php endif; ?>
                <div class="autoupdater-toggle-content" <?php if ($themes_list_count > 10) echo 'style="display: none;"'; ?>>
                    <?php foreach ($themes_list as $slug => $item) : ?>
                        <br>
                        <label for="excluded_themes_<?php echo $slug; ?>" class="checkbox">
                            <input type="checkbox" id="excluded_themes_<?php echo $slug; ?>"
                                   name="excluded_themes[]" value="theme::<?php echo $slug; ?>"
                                <?php if (!empty($item['excluded'])) echo 'checked="checked"'; ?>
                            >
                            <?php echo $item['name'] . ' (' . $slug . ')'; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>
                <?php _e('Time of day', 'autoupdater') ?>
            </label>
        </th>
        <td>
            <select name="time_of_day">
                <?php
                foreach ($time_of_day_options as $key => $item) :
                    echo '<option value="' . $key . '" ' . ($key === $time_of_day ? 'selected="selected"' : null) . '>' . $item . '</option>';
                endforeach;
                ?>
            </select>
            <p class="description">
                <?php _e('The time of the day when the automatic update is being performed', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label><?php _e('Notification e-mail', 'autoupdater') ?></label>
        </th>
        <td>
            <label>
                <input type="text" name="notification_end_user_email" value="<?php echo $notification_end_user_email; ?>" class="regular-text">
            </label>
            <p class="description">
                <?php _e('Provide an e-mail address to receive a notification after the automatic update of the site, in accordance with our hosting Privacy Policy, you have agreed on.', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label><?php _e('Notification on successful update', 'autoupdater') ?></label>
        </th>
        <td>
            <label>
                <input type="radio" name="notification_on_success" value="0" <?php if (!$notification_on_success) echo 'checked="checked"'; ?>>
                <?php _e('No') ?>
            </label>
            <label>
                <input type="radio" name="notification_on_success" value="1" <?php if ($notification_on_success) echo 'checked="checked"'; ?>>
                <?php _e('Yes') ?>
            </label>
            <p class="description">
                <?php _e('Receive a notification after a successful update', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label><?php _e('Notification on failed update', 'autoupdater') ?></label>
        </th>
        <td>
            <label>
                <input type="radio" name="notification_on_failure" value="0" <?php if (!$notification_on_failure) echo 'checked="checked"'; ?>>
                <?php _e('No') ?>
            </label>
            <label>
                <input type="radio" name="notification_on_failure" value="1" <?php if ($notification_on_failure) echo 'checked="checked"'; ?>>
                <?php _e('Yes') ?>
            </label>
            <p class="description">
                <?php _e('Receive a notification after a failed update', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    </tbody>
</table>