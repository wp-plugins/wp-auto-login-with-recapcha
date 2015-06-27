<div class="wrap">
    <div>
        <?php  
        foreach ($this->errors as  $error) {
            sprintf('<span class="errors">%s</span>',$error);
        }

        foreach ($this->msgs as  $msg) {
            sprintf('<span class="errors">%s</span>',$msg);
        }

        ?>
    </div>
    <div id="general" class="postbox">
        <table class="form-table widefat plugins wp-list-table " >
            <form method="post" action="admin.php?page=neil-auto-login-sub-page1" name="auto-login">
                <thead>
                    <h2 class="auto-login"><?php echo __('WP Auto Login Settings' , TEXT_DOMAIN ); ?> </h2>
                </thead>
                <tbody>
                    <tr  valign="top">
                        <th scope="row"><p><?php echo __('Enable Auto Login' , TEXT_DOMAIN ); ?></p></th>
                        <td>
                            <select name="auto_login" id="auto-login">
                                <?php 
                                foreach ($selectOptions as $key => $value) {
                                    $selected = $key == $autoLogin ? 'Selected = "Selected"' : '';
                                    echo '<option value="'. $key . '"'. $selected .'>' . $value .'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php if( $autoLogin != 'N' ) { ?>
                        <tr valign="top" class="redirect-url">
                            <th scope="row"><?php echo __('Login Redirect URL' , TEXT_DOMAIN ); ?></th>
                            <td>
                                <input type="text" name="login_redirect_uri" value="<?php echo esc_attr( $redirectUrl ); ?>" />
                            </td>
                        </tr>
                    <?php }?>
                    
                    <tr valign="top">
                        <td>
                            <input type="hidden" name="auto-login" value="auto-login" />
                            <?php submit_button(__('Save Changes',TEXT_DOMAIN)); ?>
                        </td>
                    </tr>
                </tbody>
            </form>
        </table>
    </div>
</div>