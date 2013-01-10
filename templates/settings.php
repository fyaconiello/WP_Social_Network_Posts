<div class="wrap">
    <h2>WP Social Network Posts</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('wp_sn_posts-group'); ?>
        <?php @do_settings_fields('wp_sn_posts-group'); ?>
        <formset>
            <legend>Facebook</legend>
            <table class="form-table">  
                <tr valign="top">
                    <th scope="row"><label for="fb_app_id">App ID</label></th>
                    <td>
                        <input type="text" name="fb_app_id" id="fb_app_id" value="<?php echo get_option('fb_app_id'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="fb_secret">Secret Key</label></th>
                    <td>
                        <input type="text" name="fb_secret" id="fb_secret" value="<?php echo get_option('fb_secret'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="fb_username">Username</label></th>
                    <td>
                        <input type="text" name="fb_username" id="fb_username" value="<?php echo get_option('fb_username'); ?>" />
                    </td>
                </tr>
            </table>
        </formset>    
        <?php @submit_button(); ?>
    </form>
</div>