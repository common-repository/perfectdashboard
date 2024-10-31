<table class="form-table">
    <tbody>
    <tr>
        <th scope="row">
            <label for="autoupdater_read_token">
                <?php _e('Read token', 'autoupdater'); ?>
            </label>
        </th>
        <td>
            <input id="autoupdater_read_token" name="read_token" type="text" class="regular-text"
                   value="<?php echo $read_token; ?>"<?php if ($protect) echo ' disabled="disabled"'; ?>
            />
            <p class="description">
                <?php _e('This token is used during reading data from the website', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="autoupdater_write_token">
                <?php _e('Write token', 'autoupdater'); ?>
            </label>
        </th>
        <td>
            <input id="autoupdater_write_token" name="write_token" type="text" class="regular-text"
                   value="<?php echo $write_token; ?>"<?php if ($protect) echo ' disabled="disabled"'; ?>
            />
            <p class="description">
                <?php _e('This token is used during making modifications to the website', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="autoupdater_aes_key">
                <?php _e('AES key', 'autoupdater'); ?>
            </label>
        </th>
        <td>
            <input id="autoupdater_aes_key" name="aes_key" type="text" class="regular-text"
                   value="<?php echo $aes_key; ?>"<?php if ($protect) echo ' disabled="disabled"'; ?>
            />
            <p class="description">
                <?php _e('This key is used to encrpyt the response if your website is not secured with the TLS', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>
                <?php _e('Response encryption', 'autoupdater'); ?>
            </label>
        </th>
        <td>
            <label>
                <input type="radio" name="encryption"
                       value="0" <?php if (empty($encryption)) echo 'checked="checked"'; ?>/>
                <?php _e('No'); ?>
            </label>
            <label>
                <input type="radio" name="encryption"
                       value="1" <?php if (!empty($encryption)) echo 'checked="checked"'; ?>/>
                <?php _e('Yes'); ?>
            </label>
            <p class="description">
                <?php _e('The response will be encrypted by the plugin if your website is not secured with the TLS', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>
                <?php _e('SSL verification', 'autoupdater'); ?>
            </label>
        </th>
        <td>
            <label>
                <input type="radio" name="ssl_verify"
                       value="0" <?php if (empty($ssl_verify)) echo 'checked="checked"'; ?>/>
                <?php _e('No'); ?>
            </label>
            <label>
                <input type="radio" name="ssl_verify"
                       value="1" <?php if (!empty($ssl_verify)) echo 'checked="checked"'; ?>/>
                <?php _e('Yes'); ?>
            </label>
            <p class="description">
                <?php _e('Enable the SSL verification for a download request', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>
                <?php _e('Site offline', 'autoupdater'); ?>
            </label>
        </th>
        <td>
            <label>
                <input type="radio" name="offline"
                       value="0" <?php if (empty($offline)) echo 'checked="checked"'; ?>/>
                <?php _e('No'); ?>
            </label>
            <label>
                <input type="radio" name="offline"
                       value="1" <?php if (!empty($offline)) echo 'checked="checked"'; ?>/>
                <?php _e('Yes'); ?>
            </label>
            <p class="description">
                <?php _e('Put your website into the maintenance mode', 'autoupdater') ?>'
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>
                <?php _e('Debug', 'autoupdater'); ?>
            </label>
        </th>
        <td>
            <label>
                <input type="radio" name="debug"
                       value="0" <?php if (empty($debug)) echo 'checked="checked"'; ?>/>
                <?php _e('No'); ?>
            </label>
            <label>
                <input type="radio" name="debug"
                       value="1" <?php if (!empty($debug)) echo 'checked="checked"'; ?>/>
                <?php _e('Yes'); ?>
            </label>
            <p class="description">
                <?php _e('Save logs to a file', 'autoupdater') ?>
            </p>
        </td>
    </tr>
    </tbody>
</table>