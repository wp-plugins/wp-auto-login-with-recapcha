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
        <table class="form-table widefat plugins wp-list-table" >
            <form method="post" action="admin.php?page=neil-auto-login-sub-page2" name="register-form">
                <thead>
                    <h2 class="auto-login"> <?php echo __('Wp Register Form Settings' , TEXT_DOMAIN ); ?> </h2>
                </thead>
                <tr valign="top">
                    <td scope="row"><p><?php echo __('Enable Password Field' , TEXT_DOMAIN ); ?></p></td>
                    <td>
                        <select name="password">
                            <?php 
                            foreach ($selectOptions as $key => $value) {
                                $selected = $key == $password ? 'Selected = "Selected"' : '';
                                echo '<option value="'. $key . '"'. $selected .'>' . $value .'</option>';
                            }
                            ?>
                        </select>

                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row"><p><?php echo __('Enable Recaptcha' , TEXT_DOMAIN ); ?></p></td>
                    <td>
                        <select name="recaptcha" id="recapcha">
                            <?php 
                            foreach ($selectOptions as $key => $value) {
                                $selected = $key == $recaptcha ? 'Selected = "Selected"' : '';
                                echo '<option value="'. $key . '"'. $selected .'>' . $value .'</option>';
                            }
                            ?>
                        </select>

                    </td>
                </tr>
                <?php if( $recaptcha != 'N' ) { ?>
                <tr valign="top" class="recapcha-text">
                    <td scope="row"><p><?php echo __('Site Key' , TEXT_DOMAIN ); ?></p></td>
                    <td>
                        <input type="text" name="sitekey" value="<?php echo esc_attr( $sitekey ); ?>" />
                    </td>
                </tr>
                <?php } ?>
                <tr valign="top">
                    <td scope="row"><p><?php echo __('Email Activation Options' , TEXT_DOMAIN ); ?><p></td>
                    <td>
                        <select name="email_activation">
                            <?php 
                            foreach ($selectOptions as $key => $value) {
                                $selected = $key == $emailActivation ? 'Selected = "Selected"' : '';
                                echo '<option value="'. $key . '"'. $selected .'>' . $value .'</option>';
                            }
                            ?>
                        </select>

                    </td>
                </tr>
                
                <tr valign="top">
                    <td>
                        <input type="hidden" name="register-password-field" value="true" />
                        <?php submit_button(__('Save Changes',TEXT_DOMAIN)); ?>
                    </td>
                </tr>
            </form>
        </table>
    </div>
</div>