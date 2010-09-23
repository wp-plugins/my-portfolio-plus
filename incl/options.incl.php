<div class="wrap">
<h2>myPortfolio Plus Settings</h2>
<form method="post" action="options.php">
    <?php settings_fields( self::WP_OPTION_GROUP ); ?>
	<h3>UI Options</h3>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Title of Projects Page</th>
        <td><input type="text" name="wpss_projects_title" value="<?php echo get_option('wpss_projects_title'); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Show Platforms Sidebar?</th>
        <td><input type="checkbox" name="wpss_show_platforms" <?php echo get_option('wpss_show_platforms') == 'on' ? 'checked="checked"' : '';  ?>/></td>
        </tr>
    </table>

	<h3>Shrink The Web Details</h3>
	<table class="form-table">
        <tr valign="top">
        <th scope="row">STW Access Key</th>
        <td><input type="text" name="wpss_stw_access" value="<?php echo get_option('wpss_stw_access'); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">STW Secret Key</th>
        <td><input type="text" name="wpss_stw_secret" value="<?php echo get_option('wpss_stw_secret'); ?>" /></td>
        </tr>
    </table>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>