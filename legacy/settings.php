<?php

function qcf_setup( $id )
{
    global  $quick_contact_form_fs ;
    $qcf_setup = qcf_get_stored_setup();
    $qcf_email = qcf_get_stored_email();
    $qcf_apikey = get_option( 'qcf_akismet' );
    
    if ( isset( $_POST['Submit'] ) && check_admin_referer( "save_qcf" ) ) {
        $options = array(
            'alternative',
            'current',
            'nostyling',
            'noui',
            'nostore'
        );
        foreach ( $options as $item ) {
            $qcf_setup[$item] = stripslashes( $_POST[$item] );
            $qcf_setup[$item] = filter_var( $qcf_setup[$item], FILTER_SANITIZE_STRING );
        }
        if ( empty($qcf_setup['current']) ) {
            $qcf_setup['current'] = '';
        }
        $arr = explode( ",", $qcf_setup['alternative'] );
        foreach ( $arr as $item ) {
            $qcf_email[$item] = stripslashes( $_POST['qcf_email' . $item] );
            $qcf_email[$item] = filter_var( $qcf_email[$item], FILTER_SANITIZE_STRING );
        }
        update_option( 'qcf_email', $qcf_email );
        update_option( 'qcf_setup', $qcf_setup );
        qcf_admin_notice( "The forms have been updated." );
    }
    
    
    if ( isset( $_POST['newform'] ) && check_admin_referer( "save_qcf" ) ) {
        $qcf_setup['alternative'] = $_POST['alternative'];
        
        if ( !empty($_POST['new_form']) ) {
            $qcf_setup['current'] = stripslashes( sanitize_text_field( $_POST['new_form'] ) );
            $qcf_setup['current'] = preg_replace( "/[^A-Za-z]/", '', $qcf_setup['current'] );
            $qcf_setup['current'] = filter_var( $qcf_setup['current'], FILTER_SANITIZE_STRING );
            $qcf_setup['alternative'] = $qcf_setup['current'] . ',' . $qcf_setup['alternative'];
            $qcf_email[$qcf_setup['current']] = stripslashes( sanitize_text_field( $_POST['new_email'] ) );
        }
        
        $qcf_email[] = $qcf_email[$qcf_setup['current']];
        update_option( 'qcf_email', $qcf_email );
        update_option( 'qcf_setup', $qcf_setup );
        qcf_admin_notice( "The new form has been added." );
        if ( $_POST['qcf_clone'] && !empty($_POST['new_form']) ) {
            qcf_clone( $qcf_setup['current'], $_POST['qcf_clone'] );
        }
    }
    
    $arr = explode( ",", $qcf_setup['alternative'] );
    foreach ( $arr as $item ) {
        
        if ( isset( $_POST['deleteform' . $item] ) && $_POST['deleteform' . $item] == $item && $_POST['delete' . $item] && $item != '' ) {
            $forms = $qcf_setup['alternative'];
            $qcf_setup['alternative'] = str_replace( $item . ',', '', $forms );
            $qcf_setup['current'] = '';
            $qcf_setup['email'] = ( isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '' );
            update_option( 'qcf_setup', $qcf_setup );
            qcf_delete_things( $item );
            qcf_admin_notice( "The form named " . $item . " has been deleted." );
            $id = '';
            break;
        }
    
    }
    
    if ( isset( $_POST['Reset'] ) && check_admin_referer( "save_qcf" ) ) {
        qcf_delete_everything();
        qcf_admin_notice( "Everything has been reset." );
        $qcf_setup = qcf_get_stored_setup();
    }
    
    
    if ( isset( $_POST['Validate'] ) && check_admin_referer( "save_qcf" ) ) {
        $apikey = sanitize_text_field( $_POST['qcf_apikey'] );
        $blogurl = get_site_url();
        $akismet = new qcf_akismet( $blogurl, $apikey );
        
        if ( $akismet->isKeyValid() ) {
            qcf_admin_notice( "Valid Akismet API Key. All messages will now be checked against the Akismet database." );
            update_option( 'qcf_akismet', $apikey );
        } else {
            qcf_admin_notice( "Your Akismet API Key is not Valid" );
        }
    
    }
    
    
    if ( isset( $_POST['Delete'] ) && check_admin_referer( "save_qcf" ) ) {
        delete_option( 'qcf_akismet' );
        qcf_admin_notice( "Akismet validation is no longer active on the Quick Contact Form" );
    }
    
    $new = '';
    
    if ( $quick_contact_form_fs->is_not_paying() ) {
        
        if ( $quick_contact_form_fs->is_trial() || $quick_contact_form_fs->is_trial_utilized() ) {
            $upurl = $quick_contact_form_fs->get_upgrade_url();
            $upmsg = esc_html__( 'Upgrade to Pro', 'quick-contact-form' );
        } else {
            $upurl = $quick_contact_form_fs->get_trial_url();
            $upmsg = esc_html__( 'Go Pro: Free 14 Day Trial', 'quick-contact-form' );
        }
        
        $new = '<div class="qpupgrade"><a href="' . $upurl . '">
    <h3>' . $upmsg . '</h3>
    <p>' . esc_html__( 'Upgrading lets you create a mailing list, send emails from your dashboard and access all form attachments.', 'quick-contact-form' ) . '</p>
    <p>' . esc_html__( 'Click here to find out more', 'quick-contact-form' ) . '</p>
    </a></div>';
    }
    
    qcf_admin_notice( null );
    $current_user = wp_get_current_user();
    $new_email = $current_user->user_email;
    if ( $qcf_setup['alternative'] == '' && $qcf_email[''] == '' ) {
        $qcf_email[''] = $new_email;
    }
    $content = '<div class="qcf-settings"><div class="qcf-options">
    <form method="post" action="">
    <h2 style="color:#B52C00">Existing Forms</h2>
    <table>
    <tr>
    <td><b>Form name&nbsp;&nbsp;</b></td><td><b>Send to this email&nbsp;&nbsp;</b></td><td><b>Shortcode</b></td><td></td>
    </tr>';
    $arr = explode( ",", $qcf_setup['alternative'] );
    sort( $arr );
    foreach ( $arr as $item ) {
        
        if ( $qcf_setup['current'] == $item ) {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        
        
        if ( $item == '' ) {
            $formname = 'default';
        } else {
            $formname = $item;
        }
        
        $content .= '<tr><td><input type="radio" name="current" value="' . esc_attr( $item ) . '" ' . esc_attr( $checked ) . ' /> ' . esc_html( $formname ) . '</td>';
        $content .= '<td><input type="text" style="padding:1px;" label="qcf_email" name="qcf_email"' . esc_attr( $item ) . '" value="' . esc_attr( $qcf_email[$item] ) . '" /></td>';
        
        if ( $item ) {
            $shortcode = ' id="' . $item . '"';
        } else {
            $shortcode = '';
        }
        
        $content .= '<td><code>[qcf' . $shortcode . ']</code></td><td>';
        if ( $item ) {
            $content .= '<input type="hidden" name="deleteform' . esc_attr( $item ) . '" value="' . esc_attr( $item ) . '">&nbsp;
<input type="submit" name="delete' . esc_attr( $item ) . '" class="qcf-button  qcf-confirm" value="delete" data-confirm="' . esc_html__( 'Are you sure you want to delete' . 'quick-contact-form ' ) . esc_attr( $item ) . '"  />';
        }
        $content .= '</td></tr>';
    }
    $content .= '</table>
    <p><input type="submit" name="Submit" class="qcf-button" value="Save Settings" />&nbsp;
    <input type="submit" name="Reset" class="qcf-button" value="Reset Everything" onclick="return window.confirm( \'This will delete all your forms and settings.\\nAre you sure you want to reset everything?\' );"/></p>
    <h2>Create New Form</h2>
    <p>Enter form name (letters only -  no numbers, spaces or punctuation marks)</p>
    <p><input type="text" label="new_Form" name="new_form" value="" /></p>
    <p>Enter your email address. To send to multiple addresses, put a comma betweeen each address.</p>
    <p><input type="text" label="new_email" name="new_email" value="' . esc_attr( $new_email ) . '" /></p>
    <input type="hidden" name="alternative" value="' . esc_attr( $qcf_setup['alternative'] ) . '" />
    <p>Copy settings from an exisiting form.</p>
    <select name="qcf_clone"><option>Do not copy settings</option>';
    foreach ( $arr as $item ) {
        if ( $item == '' ) {
            $item = 'default';
        }
        $content .= '<option value="' . $item . '">' . $item . '</option>';
    }
    $content .= '</select>
    <p><input type="submit" name="newform" class="qcf-button" value="Create New Form" /></p>';
    $as_message = '<h2>Anti Spam Protection</h2>
<div style="border: 1px solid black; padding: 10px; background-color: #ffcccb;">
    <p>Protect your forms from annoying spam</p> 
    <p>Simply install <a href="https://fullworks.net/products/anti-spam/" target="_blank">Fullworks\' Anti Spam</a></p>
    <p>No Recaptcha, no annoying quizes or images, simply effective. Free trial available.</p>
	</div>';
    $fs = array_key_exists( 'fwantispam_fs', $GLOBALS );
    
    if ( $fs ) {
        global  $fwantispam_fs ;
        if ( $fwantispam_fs->can_use_premium_code() ) {
            $as_message = '<h2>Anti Spam Protection</h2>
<div style="border: 1px solid black; padding: 10px; background-color: #90ee90;">
    <p>Brilliant - you are automatically protect from spam</p> 
    <p>By <a href="' . get_admin_url() . 'options-general.php?page=fullworks-anti-spam-settings" >Fullworks\' Anti Spam - see the settings here</a> </p>
	<p><a href="?page=quick-contact-form&tab=error">Change the anti-spam error message</a>.</p></div>';
        }
    }
    
    $content .= $as_message;
    if ( !empty($qcf_apikey) ) {
        $content .= '<h2>Use Akismet Validation</h2>
    <p>Note: Akismet is not free for commercial sites, please ensure you have an appropriate licence. Enter your API Key to check all messages against the Akismet database. <a href="?page=quick-contact-form&tab=error">Change the error message</a>.</p> 
    <p><input type="text" label="akismet" name="qcf_apikey" value="' . esc_attr( $qcf_apikey ) . '" /></p>
    <p><input type="submit" name="Validate" class="qcf-button" value="Activate Akismet Validation" />&nbsp;
<input type="submit" name="Delete" class="qcf-button" value="Deactivate Aksimet Validation" onclick="return window.confirm( \'This will delete the Akismet Key.\\nAre you sure you want to do this?\' );"/></p>';
    }
    $content .= '<h2>Global Settings</h2>';
    $content .= '<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="nostyling"' . esc_attr( $qcf_setup['nostyling'] ) . ' value="checked" /> Remove all form styles</p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="noui"' . esc_attr( $qcf_setup['noui'] ) . ' value="checked" /> Remove all jQuery  styles</p>
    <p><input style="margin:0; padding:0; border: none"type="checkbox" name="nostore" "' . esc_attr( $qcf_setup['nostore'] ) . ' value="checked"> Do not store messages in the database</p>
 ';
    $content .= wp_nonce_field( "save_qcf" );
    $content .= '</form>
    </div>
    <div class="qcf-options" style="float:right">
    <h2>Adding the contact form to your site</h2>
    <p>To add the basic contact form to your posts or pages use the shortcode: <code>[qcf]</code>.<br />
    <p>If you have a named form the shortcode is <code>[qcf id="form name"]</code>.<br />
    <p>To add the form to your theme files use <code>&lt;?php echo do_shortcode("[qcf]"); ?&gt;</code></p>
    <p>There is also a widget called "Quick Contact Form" you can drag and drop into a sidebar.</p>
    <p>That\'s it. The form is ready to use.</p>';
    if ( $quick_contact_form_fs->is_not_paying() ) {
        $content .= $new;
    }
    $content .= '<h2>Options and Settings</h2>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form&tab=settings">Form Settings.</a></span> Change the layout of the form, add or remove fields and the order they appear and edit the labels and captions.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form&tab=attach">Attachments.</a></span> Set how the form handles attachments.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form&tab=styles">Styling.</a></span> Change fonts, colours, borders, images and submit button.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form&tab=send">Send Options.</a></span> Change the thank you message and how the form is sent.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form&tab=autoresponce">Auto Responder.</a></span> Send rich content messages to your visitors.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form&tab=error">Error Messages.</a></span> Change the error message.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form&tab=mailinglist">' . esc_html__( 'Mailchimp', 'quick-contact-form' ) . '.</a></span> ' . esc_html__( 'Add your visitors to a mailchimp list.', 'quick-contact-form' );
    if ( $quick_contact_form_fs->is_not_paying() ) {
        $content .= ' ' . esc_html__( 'QCF Pro users only.', 'quick-contact-form' );
    }
    $content .= '</p>';
    $content .= '<p><span style="font-weight:bold"><a href="?page=quick-contact-form&tab=buildlist">' . esc_html__( 'Mail Lists', 'quick-contact-form' ) . '.</a></span> ' . esc_html__( 'Build an email list and send messages.', 'quick-contact-form' );
    if ( $quick_contact_form_fs->is_not_paying() ) {
        $content .= ' ' . esc_html__( 'QCF Pro users only.', 'quick-contact-form' );
    }
    $content .= '</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form-messages">Message Centre.</a></span> See all messages. Or click on the <b>Message</b> link in the dashboard menu.</p>';
    
    if ( $quick_contact_form_fs->is_not_paying() ) {
        global  $quick_contact_form_fs ;
        
        if ( $quick_contact_form_fs->is_trial() || $quick_contact_form_fs->is_trial_utilized() ) {
            $upurl = $quick_contact_form_fs->get_upgrade_url();
            $upmsg = esc_html__( 'Upgrade to Pro', 'quick-contact-form' );
        } else {
            $upurl = $quick_contact_form_fs->get_trial_url();
            $upmsg = esc_html__( 'Go Pro: Free 14 Day Trial', 'quick-contact-form' );
        }
        
        $content .= '<h2>Free Plugin Support</h2>
    <p>If you have any questions visit the <a href="https://fullworks.net/docs/quick-contact-form/">plugin knowledge base</a> or use the free plugin community support forum  at <a href="https://wordpress.org/support/plugin/quick-contact-form/">wordpress.org</a>.</p>';
    } else {
        $content .= '<h2>Pro Plugin Support</h2>
    <p>Use the <a href="' . admin_url( 'options-general.php?page=quick-contact-form-contact' ) . '">Contact Tab</a>.</p>';
    }
    
    $content .= '</div></div>';
    echo  $content ;
}

function qcf_form_settings( $id )
{
    $active_buttons = array(
        'field1',
        'field2',
        'field3',
        'field4',
        'field5',
        'field6',
        'field7',
        'field8',
        'field9',
        'field10',
        'field11',
        'field12',
        'field13',
        'field14',
        'field15'
    );
    qcf_change_form_update();
    
    if ( isset( $_POST['Submit'] ) && check_admin_referer( "save_qcf" ) ) {
        foreach ( $active_buttons as $item ) {
            $qcf['active_buttons'][$item] = ( (isset( $_POST['qcf_settings_active_' . $item] ) and $_POST['qcf_settings_active_' . $item] == 'on') ? true : false );
            $qcf['required'][$item] = isset( $_POST['required_' . $item] );
            
            if ( !empty($_POST['label_' . $item]) ) {
                
                if ( 'field15' == $item ) {
                    $qcf['label'][$item] = wp_kses_post( stripslashes( $_POST['label_' . $item] ) );
                    continue;
                }
                
                $qcf['label'][$item] = sanitize_text_field( stripslashes( $_POST['label_' . $item] ) );
                //			$qcf['label'][ $item ] = str_replace( "'", "&#8217;", $qcf['label'][ $item ] );
            }
        
        }
        $qcf['dropdownlist'] = str_replace( ', ', ',', sanitize_text_field( $_POST['dropdown_string'] ) );
        $qcf['checklist'] = str_replace( ', ', ',', sanitize_text_field( $_POST['checklist_string'] ) );
        $qcf['radiolist'] = str_replace( ', ', ',', sanitize_text_field( $_POST['radio_string'] ) );
        $qcf['required']['field12'] = 'checked';
        $options = array(
            'showuser',
            'sort',
            'lines',
            'title',
            'blurb',
            'border',
            'send',
            'datepicker',
            'fieldtype',
            'fieldtypeb',
            'selectora',
            'selectorb',
            'selectorc',
            'min',
            'max',
            'initial',
            'step'
        );
        foreach ( $options as $item ) {
            if ( isset( $_POST[$item] ) ) {
                $qcf[$item] = sanitize_text_field( stripslashes( $_POST[$item] ) );
            }
        }
        foreach ( $active_buttons as $button ) {
            if ( isset( $_POST['label_' . $button] ) ) {
                $qcf['label_' . $button] = wp_kses_post( stripslashes( $_POST['label_' . $button] ) );
            }
        }
        update_option( 'qcf_settings' . $id, $qcf );
        
        if ( $id ) {
            qcf_admin_notice( esc_html__( 'The form settings for', 'quick-contact-form' ) . ' ' . $id . ' ' . esc_html__( 'have been updated.', 'quick-contact-form' ) );
        } else {
            qcf_admin_notice( esc_html__( 'The default form settings have been updated.', 'quick-contact-form' ) );
        }
    
    }
    
    
    if ( isset( $_POST['Reset'] ) && check_admin_referer( "save_qcf" ) ) {
        delete_option( 'qcf_settings' . $id );
        
        if ( $id ) {
            qcf_admin_notice( esc_html__( 'The form settings for', 'quick-contact-form' ) . ' ' . $id . ' ' . esc_html__( 'have been reset.', 'quick-contact-form' ) );
        } else {
            qcf_admin_notice( esc_html__( 'The default form settings have been reset.', 'quick-contact-form' ) );
        }
    
    }
    
    $qcf_setup = qcf_get_stored_setup();
    $id = $qcf_setup['current'];
    $qcf = qcf_get_stored_options( $id );
    $content = '<script>
    jQuery(function() {
    var qcf_sort = jQuery( "#qcf_sort" ).sortable({ axis: "y" ,
    update:function(e,ui) {
    var order = qcf_sort.sortable("toArray").join();
    jQuery("#qcf_settings_sort").val(order);
    }
    });
    });
    </script>';
    $content .= qcf_head_css();
    $content .= '<div class="qcf-settings"><div class="qcf-options">';
    
    if ( $id ) {
        $content .= '<h2 style="color:#B52C00">Form settings for ' . $id . '</h2>';
    } else {
        $content .= '<h2 style="color:#B52C00">Default form settings</h2>';
    }
    
    $content .= qcf_change_form( $qcf_setup );
    $content .= '<form id="qcf_settings_form" method="post" action="">
    <h2>Form Title and Introductory Blurb</h2>
    <p>Form title (leave blank if you don\'t want a heading):</p>
    <p><input type="text" name="title" value="' . esc_attr( $qcf['title'] ) . '" /></p>
    <p>This is the blurb that will appear below the heading and above the form (leave blank if you don\'t want any blurb):</p>
    <p><input type="text" name="blurb" value="' . esc_attr( $qcf['blurb'] ) . '" /></p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="showuser"' . esc_attr( $qcf['showuser'] ) . ' value="checked" /> Autofill name and email if user is logged in.</p>
    <h2>Form Fields</h2>
    <p>Drag and drop to change order of the fields.</p>
    <div style="margin-left:7px;font-weight:bold;">
    <div style="float:left; width:20%;">Field Selection</div>
    <div style="float:left; width:30%;">Label</div>
    <div style="float:left;">Required field</div>
    </div>
    <div style="clear:left"></div>
    <ul id="qcf_sort">';
    foreach ( explode( ',', $qcf['sort'] ) as $name ) {
        $checked = ( $qcf['active_buttons'][$name] ? 'checked' : '' );
        $required = ( $qcf['required'][$name] ? 'checked' : '' );
        $datepicker = ( $qcf['datepicker'] ? 'checked' : '' );
        $lines = $qcf['lines'];
        $options = '';
        switch ( $name ) {
            case 'field1':
                $type = esc_html__( 'Textbox', 'quick-contact-form' );
                $options = '';
                break;
            case 'field2':
                $type = esc_html__( 'Email', 'quick-contact-form' );
                $options = ' ' . esc_html__( 'also validates format', 'quick-contact-form' );
                break;
            case 'field3':
                $type = esc_html__( 'Telephone', 'quick-contact-form' );
                $options = esc_html__( 'also checks number format', 'quick-contact-form' );
                break;
            case 'field4':
                $type = esc_html__( 'Textarea', 'quick-contact-form' );
                $options = 'Number of rows: <input type="text" style="border:1px solid #415063; width:3em;" name="lines" value ="' . esc_attr( $qcf['lines'] ) . '" /><br>
            Allowed Tags:<br><input type="text" style="border:1px solid #415063;" name="htmltags" value="' . esc_attr( $qcf['htmltags'] ) . '" />';
                break;
            case 'field5':
                $type = esc_html__( 'Selector', 'quick-contact-form' );
                $options = '<input type="radio" name="selectora" value="dropdowna" ' . checked( $qcf['selectora'], 'dropdowna', false ) . ' />&nbsp;Dropdown
            <input type="radio" name="selectora" value="checkboxa" ' . checked( $qcf['selectora'], 'checkboxa', false ) . ' />&nbsp;Checkbox
            <input type="radio" name="selectora" value="radioa" ' . checked( $qcf['selectora'], 'radioa', false ) . ' />&nbsp;Radio<br>
            <span class="description">Options (separate with a comma):</span><br><textarea name="dropdown_string" label="Dropdown" rows="2">' . esc_html( $qcf['dropdownlist'] ) . '</textarea>';
                break;
            case 'field6':
                $type = esc_html__( 'Selector', 'quick-contact-form' );
                $options = '<input type="radio" name="selectorb" value="dropdownb" ' . checked( $qcf['selectorb'], 'dropdownb', false ) . ' />&nbsp;Dropdown
            <input type="radio" name="selectorb" value="checkboxb" ' . checked( $qcf['selectorb'], 'checkboxb', false ) . ' />&nbsp;Checkbox
            <input type="radio" name="selectorb" value="radiob" ' . checked( $qcf['selectorb'], 'radiob', false ) . ' />&nbsp;Radio<br>
            <span class="description">Options (separate with a comma):</span><br><textarea  name="checklist_string" label="Checklist" rows="2">' . esc_html( $qcf['checklist'] ) . '</textarea>';
                break;
            case 'field7':
                $type = esc_html__( 'Selector', 'quick-contact-form' );
                $options = '<input type="radio" name="selectorc" value="dropdownc" ' . checked( $qcf['selectorc'], 'dropdownc', false ) . ' />&nbsp;Dropdown
            <input type="radio" name="selectorc" value="checkboxc" ' . checked( $qcf['selectorc'], 'checkboxc', false ) . ' />&nbsp;Checkbox
            <input type="radio" name="selectorc" value="radioc" ' . checked( $qcf['selectorc'], 'radioc', false ) . ' />&nbsp;Radio<br>
            <span class="description">Options (separate with a comma):</span><br><textarea  name="radio_string" label="Radio" rows="2">' . esc_html( $qcf['radiolist'] ) . '</textarea>';
                break;
            case 'field8':
                $type = esc_html__( 'Textbox', 'quick-contact-form' );
                $options = '';
                break;
            case 'field9':
                $type = esc_html__( 'Textbox', 'quick-contact-form' );
                $options = '';
                break;
            case 'field10':
                $type = 'Date';
                $options = '';
                break;
            case 'field11':
                $type = 'Multibox';
                $options = '<input type="radio" name="fieldtype" value="ttext" ' . checked( $qcf['fieldtype'], 'ttext', false ) . ' />&nbsp;Text
<input type="radio" name="fieldtype" value="tmail" ' . checked( $qcf['fieldtype'], 'tmail', false ) . ' />&nbsp;Email
<input type="radio" name="fieldtype" value="ttele" ' . checked( $qcf['fieldtype'], 'ttele', false ) . ' />&nbsp;Telephone
<input type="radio" name="fieldtype" value="tdate" ' . checked( $qcf['fieldtype'], 'tdate', false ) . ' />&nbsp;Date';
                break;
            case 'field12':
                $type = 'Maths Captcha';
                $options = '<span class="description">Add a maths checker to the form to (hopefully) block most of the spambots.</span>' . '<h2>Need better protection?</h2>' . qcf_antispam_message();
                break;
            case 'field13':
                $type = 'Multibox';
                $options = '<input type="radio" name="fieldtypeb" value="btext" ' . checked( $qcf['fieldtypeb'], 'btext', false ) . ' />&nbsp;Text
<input type="radio" name="fieldtypeb" value="bmail" ' . checked( $qcf['fieldtypeb'], 'bmail', false ) . ' />&nbsp;Email
<input type="radio" name="fieldtypeb" value="btele" ' . checked( $qcf['fieldtypeb'], 'btele', false ) . ' />&nbsp;Telephone
<input type="radio" name="fieldtypeb" value="bdate" ' . checked( $qcf['fieldtypeb'], 'bdate', false ) . ' />&nbsp;Date';
                break;
            case 'field14':
                $type = 'Range slider';
                $options = '<input type="text" style="border:1px solid #415063; width:3em;" name="min" value ="' . esc_attr( $qcf['min'] ) . '" />&nbsp;Minimum value<br>
<input type="text" style="border:1px solid #415063; width:3em;" name="max" value ="' . esc_attr( $qcf['max'] ) . '" />&nbsp;Maximum value<br>
<input type="text" style="border:1px solid #415063; width:3em;" name="initial" value ="' . esc_attr( $qcf['initial'] ) . '" />&nbsp;Initial value<br>
<input type="text" style="border:1px solid #415063; width:3em;" name="step" value ="' . esc_attr( $qcf['step'] ) . '" />&nbsp;Step';
                break;
            case 'field15':
                $type = esc_html__( 'Consent', 'quick-contact-form' );
                $options = '<span class="description">' . esc_html__( 'Add a checkbox to permit data storage. html links are permitted', 'quick-contact-form' ) . '</span>';
                break;
        }
        $li_class = ( $checked ? 'button_active' : 'button_inactive' );
        $input_tag = '<input type="text" style="border: border:1px solid #415063; padding: 1px; margin:0;" name="label_' . esc_attr( $name ) . '" value="' . esc_attr( $qcf['label'][$name] ) . '"/>';
        if ( 'field15' == $name ) {
            $input_tag = '<textarea rows="5" style="border: border:1px solid #415063; padding: 1px; margin:0;" name="label_' . esc_attr( $name ) . '" >' . wp_kses_post( $qcf['label'][$name] ) . '</textarea>';
        }
        $content .= '<li class="' . esc_attr( $li_class ) . '" id="' . esc_attr( $name ) . '">
        <div style="float:left; width:20%;">
        <input type="checkbox" class="button_activate" style="border: none;" name="qcf_settings_active_' . esc_attr( $name ) . '" ' . esc_attr( $checked ) . ' />' . esc_html( $type ) . '</div>
        <div style="float:left; width:30%;">' . qcf_kses_settings( $input_tag ) . '</div>
        <div style="float:left;width:5%">';
        $exclude = array( "field12" );
        
        if ( !in_array( $name, $exclude ) ) {
            $content .= '<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0 0 0 5px;" name="required_' . esc_attr( $name ) . '" ' . esc_attr( $required ) . ' /> ';
        } else {
            $content .= '&nbsp;';
        }
        
        $content .= '</div><div style="float:left;width:45%">' . qcf_kses_settings( $options ) . '</div><div style="clear:left"></div></li>';
    }
    $content .= '</ul>
    <input type="hidden" id="qcf_settings_sort" name="sort" value="' . esc_attr( $qcf['sort'] ) . '" />
    <h2>Submit button caption</h2>
    <p><input type="text" style="text-align:center" name="send" value="' . esc_attr( $qcf['send'] ) . '" /></p>
    <p><input type="submit" name="Submit" class="qcf-button" value="Save Changes" /> &nbsp;
<input type="submit" name="Reset" class="qcf-button" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset these settings?\' );"/></p>';
    $content .= wp_nonce_field( "save_qcf" );
    $content .= '</form>
    </div>
    <div class="qcf-options" style="float:right">
    <h2 style="color:#B52C00">Form Preview</h2>
    <p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won\'t look exactly like the one below.</p>';
    $content .= qcf_loop( $id );
    $content .= '<p>Have you set up the <a href="?page=quick-contact-form&tab=reply">reply options</a>?</p>
    <p>You can also customise the <a href="?page=quick-contact-form&tab=error">error messages</a>.</p>
    </div>
    </div>';
    echo  $content ;
}

function qcf_attach( $id )
{
    qcf_change_form_update();
    
    if ( isset( $_POST['Submit'] ) && check_admin_referer( "save_qcf" ) ) {
        $options = array(
            'qcf_attach',
            'qcf_number',
            'qcf_required',
            'qcf_attach_label',
            'qcf_attach_size',
            'qcf_attach_type',
            'qcf_attach_width',
            'qcf_attach_error',
            'qcf_attach_error_size',
            'qcf_attach_error_type',
            'qcf_attach_link',
            'qcf_attach_error_required'
        );
        foreach ( $options as $item ) {
            $attach[$item] = stripslashes( sanitize_text_field( $_POST[$item] ) );
            $attach[$item] = filter_var( $attach[$item], FILTER_SANITIZE_STRING );
        }
        update_option( 'qcf_attach' . $id, $attach );
        
        if ( $id ) {
            qcf_admin_notice( "The attachment settings for " . $id . " have been updated." );
        } else {
            qcf_admin_notice( "The default form settings have been reset." );
        }
    
    }
    
    
    if ( isset( $_POST['Reset'] ) && check_admin_referer( "save_qcf" ) ) {
        delete_option( 'qcf_attach' . $id );
        
        if ( $id ) {
            qcf_admin_notice( "The attachment settings for " . $id . " have been reset." );
        } else {
            qcf_admin_notice( "The default form settings have been reset." );
        }
    
    }
    
    $qcf_setup = qcf_get_stored_setup();
    $id = $qcf_setup['current'];
    $attach = qcf_get_stored_attach( $id );
    $content = qcf_head_css();
    $content .= '<div class="qcf-settings"><div class="qcf-options">';
    
    if ( $id ) {
        $content .= '<h2 style="color:#B52C00">Attachment options for ' . esc_html( $id ) . '</h2>';
    } else {
        $content .= '<h2 style="color:#B52C00">Default attachment options</h2>';
    }
    
    $content .= qcf_change_form( $qcf_setup );
    $content .= '<p>If you want your visitors to attach files then use these settings. Take care not to let them attach system files, executables, trojans, worms and a other nasties!</p>
    <form id="qcf_settings_form" method="post" action="">
    <table>
    <tr>
    <td colspan="2"><h2>Attachment Settings</h2></td>
    </tr>
    <tr>
    <td></td>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_attach"' . esc_attr( $attach['qcf_attach'] ) . ' value="checked" /> User can attach files</td>
    </tr>
    <tr>
    <td></td>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_required"' . esc_attr( $attach['qcf_required'] ) . ' value="checked" /> Attachment is required</td>
    </tr>
    <tr>
    <td>Max Number of attachments</td>
    <td><input type="text" style="width:3em" name="qcf_number" value="' . esc_attr( $attach['qcf_number'] ) . '" /></td>
    </tr>
    <tr>
    <td>Field Label</td>
    <td><input type="text" name="qcf_attach_label" value="' . esc_attr( $attach['qcf_attach_label'] ) . '" /></td>
    </tr>
    <tr>
    <td>Maximum File size</td>
    <td><input type="text" name="qcf_attach_size" value="' . esc_attr( $attach['qcf_attach_size'] ) . '" /></td>
    </tr>
    <tr>
    <td>Allowable file types</td>
    <td><input type="text" name="qcf_attach_type" value="' . esc_attr( $attach['qcf_attach_type'] ) . '" /></td>
    </tr>
    <tr>
    <td>Field size</td>
    <td><p>This is a trial and error number. You can\'t use a \'width\' style as the size is a number of characters. Test using the live form not the preview. Note also that many browsers will ignore your settings.</p>
    <p><em>Example: A form width of 280px with a plain border has field width of about 15. With no border it\'s about 18.</em></p>
    <p><input type="text" style="width:5em;" name="qcf_attach_width" value="' . esc_attr( $attach['qcf_attach_width'] ) . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Error messages</h2></td>
    </tr>
    <tr>
    <td>General Errors:</td>
    <td><input type="text" name="qcf_attach_error" value="' . esc_attr( $attach['qcf_attach_error'] ) . '" /></td>
    </tr>
    <tr>
    <td>If the file is too big:</td>
    <td><input type="text" name="qcf_attach_error_size" value="' . esc_attr( $attach['qcf_attach_error_size'] ) . '" /></td>
    </tr>
    <tr>
    <td>If the filetype is incorrect:</td>
    <td><input type="text" name="qcf_attach_error_type" value="' . esc_attr( $attach['qcf_attach_error_type'] ) . '" /></td>
    </tr>
    <tr>
    <td>Missing attachment:</td>
    <td><input type="text" name="qcf_attach_error_required" value="' . esc_attr( $attach['qcf_attach_error_required'] ) . '" /></td>
    </tr>
    </table>
    <p>All attachments are uploaded to a folder called \'qcf\' in your media library. The checkbox below adds links to the documents in the email instead of attaching them. Useful if you are allowing multiple or very large files.</p>
    <p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_attach_link"' . esc_attr( $attach['qcf_attach_link'] ) . ' value="checked" /> Add attachment links to the email.</p>
    <p><input type="submit" name="Submit" class="qcf-button" value="Save Changes" />&nbsp;
<input type="submit" name="Reset" class="qcf-button" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the attachment settings for ' . esc_attr( $id ) . '?\' );"/></p>';
    $content .= wp_nonce_field( "save_qcf" );
    $content .= '</form>
    </div>
    <div class="qcf-options" style="float:right"> 
    <h2 style="color:#B52C00">Form Preview</h2>
    <p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won\'t look exactly like the one below.</p>';
    $content .= qcf_loop( $id );
    $content .= '</div></div>';
    echo  $content ;
}

function qcf_styles( $id )
{
    qcf_change_form_update();
    
    if ( isset( $_POST['Submit'] ) && check_admin_referer( "save_qcf" ) ) {
        $options = array(
            'font',
            'font-family',
            'font-size',
            'font-colour',
            'text-font-family',
            'text-font-size',
            'text-font-colour',
            'input-border',
            'input-required',
            'inputbackground',
            'inputfocus',
            'border',
            'width',
            'widthtype',
            'submitwidth',
            'submitwidthset',
            'submitposition',
            'background',
            'backgroundhex',
            'backgroundimage',
            'corners',
            'styles',
            'usetheme',
            'submit-colour',
            'submit-background',
            'submit-hover-background',
            'submit-border',
            'submit-button',
            'form-border',
            'header',
            'header-type',
            'header-size',
            'header-colour',
            'error-font-colour',
            'error-border',
            'slider-thickness',
            'slider-background',
            'slider-revealed',
            'handle-background',
            'handle-border',
            'handle-corners',
            'output-size',
            'output-colour',
            'nostyling',
            'line_margin'
        );
        foreach ( $options as $item ) {
            $style[$item] = stripslashes( $_POST[$item] );
            $style[$item] = filter_var( $style[$item], FILTER_SANITIZE_STRING );
        }
        
        if ( $style['widthtype'] == 'pixel' ) {
            $formwidth = preg_split( '#(?<=\\d)(?=[a-z%])#i', $style['width'] );
            if ( !$formwidth[1] ) {
                $formwidth[1] = 'px';
            }
            $style['width'] = $formwidth[0] . $formwidth[1];
        }
        
        update_option( 'qcf_style' . $id, $style );
        echo  '<style>' . qcf_generate_css() . '</style>' ;
        qcf_admin_notice( "The form styles have been updated." );
    }
    
    
    if ( isset( $_POST['Reset'] ) && check_admin_referer( "save_qcf" ) ) {
        delete_option( 'qcf_style' . $id );
        
        if ( $id ) {
            qcf_admin_notice( "The style settings for " . $id . " have been reset." );
        } else {
            qcf_admin_notice( "The default form settings have been updated." );
        }
    
    }
    
    $qcf_setup = qcf_get_stored_setup();
    $id = $qcf_setup['current'];
    $qcf = qcf_get_stored_options( $id );
    $style = qcf_get_stored_style( $id );
    $content = qcf_head_css();
    $content .= '<div class="qcf-settings"><div class="qcf-options">';
    
    if ( $id ) {
        $content .= '<h2 style="color:#B52C00">Styles for ' . esc_html( $id ) . '</h2>';
    } else {
        $content .= '<h2 style="color:#B52C00">Default form styles</h2>';
    }
    
    $content .= qcf_change_form( $qcf_setup );
    $content .= '<form method="post" action="">
    <span class="description"><b>NOTE:</b>Leave fields blank if you don\'t want to use them</span>
    <table>
    <tr><td colspan="2"><h2>Form Width</h2></td></tr>
    <tr>
    <td></td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="percent" ' . checked( $style['widthtype'], 'percent', false ) . ' /> 100% (fill the available space)<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="pixel" ' . checked( $style['widthtype'], 'pixel', false ) . ' /> Fixed :&nbsp;
<input type="text" style="width:4em" label="width" name="width" value="' . esc_attr( $style['width'] ) . '" /> use px, em or %. Default is px.</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Form Border</h2>
    </td>
    </tr>
    <tr>
    <td>Type:</td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="border" value="none" ' . checked( $style['border'], 'none', false ) . ' /> No border<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="border" value="plain" ' . checked( $style['border'], 'plain', false ) . ' /> Plain Border<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="border" value="rounded" ' . checked( $style['border'], 'rounded', false ) . ' /> Round Corners (Not IE8)<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="border" value="shadow" ' . checked( $style['border'], 'shadow', false ) . ' /> Shadowed Border(Not IE8)<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="border" value="roundshadow" ' . checked( $style['border'], 'roundshadow', false ) . ' /> Rounded Shadowed Border (Not IE8)</td>
    </tr>
    <tr>
    <td>Style:</td>
    <td><input type="text" label="form-border" name="form-border" value="' . esc_attr( $style['form-border'] ) . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Background</h2></td>
    </tr>
    <tr>
    <td>Colour:</td>
    <td>
    <input style="margin:0; padding:0; border:none;" type="radio" name="background" value="theme" ' . checked( $style['background'], 'theme', false ) . ' /> Use theme colours<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="background" value="white" ' . checked( $style['background'], 'white', false ) . ' /> White<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="background" value="color" ' . checked( $style['background'], 'color', false ) . ' />	Set your own: 
    <input type="text" class="qcf-color" label="background" name="backgroundhex" value="' . esc_attr( $style['backgroundhex'] ) . '" /></td>
    </tr>
    <tr>
    <td>Background<br>Image:</td>
    <td><input id="qcf_background" type="text" name="backgroundimage" value="' . esc_attr( $style['backgroundimage'] ) . '" />
    <input id="qcf_upload_background" class="button" name="bg" type="button" value="Upload Image" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Font Styles</h2></td>
    </tr>
    <tr>
    <td></td>
    <td><input type="radio" name="font" value="theme" ' . checked( $style['font'], 'theme', false ) . ' /> Use theme font styles<br />
    <input type="radio" name="font" value="plugin" ' . checked( $style['font'], 'plugin', false ) . ' /> Use Plugin font styles (enter font family and size below)
    </td>
    </tr>
    <tr>
    <td colspan="2"><h2>Form Header</h2></td>
    </tr>
<tr>
    <td style="vertical-align:top;">' . esc_html__( 'Header Type', 'quick-event-manager' ) . '</td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="header-type" value="h2" ' . checked( $style['header-type'], 'h2', false ) . ' /> H2 
    <input style="margin:0; padding:0; border:none;" type="radio" name="header-type" value="h3" ' . checked( $style['header-type'], 'h3', false ) . ' /> H3 
    <input style="margin:0; padding:0; border:none;" type="radio" name="header-type" value="h4" ' . checked( $style['header-type'], 'h4', false ) . ' /> H4</td>
    </tr>
    <tr>
    <td>Header Size: </td>
    <td><input type="text" style="width:6em" label="header-size" name="header-size" value="' . esc_attr( $style['header-size'] ) . '" /></td>
    </tr>
    <tr>
    <td>Header Colour: </td>
    <td><input type="text" class="qcf-color" label="header-colour" name="header-colour" value="' . esc_attr( $style['header-colour'] ) . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Input Fields</h2></td>
    </tr>
    <tr>
    <td>Font Family: </td>
    <td><input type="text" label="font-family" name="font-family" value="' . esc_attr( $style['font-family'] ) . '" /></td></tr>
    <tr>
    <td>Font Size: </td>
    <td><input type="text" style="width:6em" label="font-size" name="font-size" value="' . esc_attr( $style['font-size'] ) . '" /></td>
    </tr>
    <tr>
    <td>Font Colour: </td>
    <td><input type="text" class="qcf-color" label="font-colour" name="font-colour" value="' . esc_attr( $style['font-colour'] ) . '" /></td>
    </tr>
    <tr>
    <td>Normal Border: </td>
    <td><input type="text" label="input-border" name="input-border" value="' . esc_attr( $style['input-border'] ) . '" /></td>
    </tr>
    <tr>
    <td>Required Fields: </td>
    <td><input type="text" label="input-required" name="input-required" value="' . esc_attr( $style['input-required'] ) . '" /></td>
    </tr>
    <tr>
    <td>Background: </td>
    <td><input type="text" class="qcf-color" label="inputbackground" name="inputbackground" value="' . esc_attr( $style['inputbackground'] ) . '" /></td>
    </tr>
    <tr>
    <td>Focus: </td>
    <td><input type="text" class="qcf-color" label="inputfocus" name="inputfocus" value="' . esc_attr( $style['inputfocus'] ) . '" /></td>
    </tr>
    <tr><td>Corners: </td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="corners" value="corner" ' . checked( $style['corners'], 'corner', false ) . ' /> Use theme settings 
    <input style="margin:0; padding:0; border:none;" type="radio" name="corners" value="square" ' . checked( $style['corners'], 'square', false ) . ' /> Square corners 	
    <input style="margin:0; padding:0; border:none;" type="radio" name="corners" value="round" ' . checked( $style['corners'], 'round', false ) . ' /> 5px rounded corners</td>
    </tr>
    <tr>
    <td style="vertical-align:top;">' . esc_html__( 'Margins and Padding', 'quick-event-manager' ) . '</td>
    <td><span class="description">' . esc_html__( 'Set the margins and padding of each bit using CSS shortcodes', 'quick-contact-form' ) . ':</span><br>
    <input type="text" label="line margin" name="line_margin" value="' . esc_attr( $style['line_margin'] ) . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Other text content</h2></td>
    </tr>
    <tr>
    <td>Font Family: </td>
    <td><input type="text" label="text-font-family" name="text-font-family" value="' . esc_attr( $style['text-font-family'] ) . '" /></td>
    </tr>
    <tr>
    <td>Font Size: </td>
    <td><input type="text" style="width:6em" label="text-font-size" name="text-font-size" value="' . esc_attr( $style['text-font-size'] ) . '" /></td>
    </tr>
    <tr>
    <td>Font Colour: </td>
    <td><input type="text" class="qcf-color" label="text-font-colour" name="text-font-colour" value="' . esc_attr( $style['text-font-colour'] ) . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Error Messages</h2></td>
    </tr>
    <tr><td>Font Colour: </td>
    <td><input type="text" class="qcf-color" label="error-font-colour" name="error-font-colour" value="' . esc_attr( $style['error-font-colour'] ) . '" /></td>
    </tr>
    <tr>
    <td>Error Border: </td><td><input type="text" label="error-border" name="error-border" value="' . esc_attr( $style['error-border'] ) . '" /></td></tr>
    <tr>
    <td td colspan="2"><h2>Submit Button</h2></td></tr>
    <tr>
    <td>Font Colour: </td><td><input type="text" class="qcf-color" label="submit-colour" name="submit-colour" value="' . esc_attr( $style['submit-colour'] ) . '" /></td></tr>
    <tr>
    <td>Background: </td><td><input type="text" class="qcf-color" label="submit-background" name="submit-background" value="' . esc_attr( $style['submit-background'] ) . '" /></td>
    </tr>
    <tr>
    <td>Hover: </td><td><input type="text" class="qcf-color" label="submit-hover-background" name="submit-hover-background" value="' . esc_attr( $style['submit-hover-background'] ) . '" /></td>
    </tr>
    <tr>
    <td>Border: </td><td><input type="text" label="submit-border" name="submit-border" value="' . esc_attr( $style['submit-border'] ) . '" /></td></tr>
    <tr>
    <td>Size: </td><td><input style="margin:0; padding:0; border:none;" type="radio" name="submitwidth" value="submitpercent" ' . checked( $style['submitwidth'], 'submitpercent', false ) . ' /> Same width as the form<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="submitwidth" value="submitrandom" ' . checked( $style['submitwidth'], 'submitrandom', false ) . ' /> Same width as the button text<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="submitwidth" value="submitpixel" ' . checked( $style['submitwidth'], 'submitpeixel', false ) . ' /> Set your own width:&nbsp;
<input type="text" style="width:5em" label="submitwidthset" name="submitwidthset" value="' . esc_attr( $style['submitwidthset'] ) . '" /> (px, % or em)</td>
    </tr>
    <tr>
    <td>Position: </td><td><input style="margin:0; padding:0; border:none;" type="radio" name="submitposition" value="submitleft" ' . checked( $style['submitposition'], 'submitleft', false ) . ' /> Left 
    <input style="margin:0; padding:0; border:none;" type="radio" name="submitposition" value="submitright" ' . checked( $style['submitposition'], 'submitright', false ) . ' /> Right</td></tr>
    <tr>
    <td>Button Image: </td><td>
    <input id="qcf_submit_button" type="text" name="submit-button" value="' . esc_attr( $style['submit-button'] ) . '" />
    <input id="qcf_upload_submit_button" class="qcf-button" name="sb" type="button" value="Upload Image" /></td></tr>';
    if ( $qcf['active_buttons']['field14'] ) {
        $content .= '<tr>
    <td colspan="2"><h2>Slider</h2></td>
    </tr>
    <tr>
    <td>Thickness</td>
    <td><input type="number" min="1" style="width:6em" label="input-border" name="slider-thickness" value="' . esc_attr( $style['slider-thickness'] ) . '" />em</td>
    </tr>
    <tr>
    <td>Normal Background</td>
    <td><input type="text" class="qcf-color" label="input-border" name="slider-background" value="' . esc_attr( $style['slider-background'] ) . '" /></td>
    </tr>
    <tr>
    <td>Revealed Background</td>
    <td><input type="text" class="qcf-color" label="input-border" name="slider-revealed" value="' . esc_attr( $style['slider-revealed'] ) . '" /></td>
    </tr>
    <tr>
    <td>Handle Background</td>
    <td><input type="text" class="qcf-color" label="input-border" name="handle-background" value="' . esc_attr( $style['handle-background'] ) . '" /></td>
    </tr>
    <tr>
    <td>Handle Border</td>
    <td><input type="text" class="qcf-color" label="input-border" name="handle-border" value="' . esc_attr( $style['handle-border'] ) . '" /></td>
    </tr>
    <tr>
    <td>Corners</td>
    <td><input type="number" min="1" style="width:6em" name="handle-corners" value="' . esc_attr( $style['handle-corners'] ) . '" />&nbsp;%</td>
    </tr>
    <tr>
    <td>Output Size</td>
    <td><input type="text" style="width:3em" label="input-border" name="output-size" value="' . esc_attr( $style['output-size'] ) . '" /></td>
    </tr>
    <tr>
    <td>Output Colour</td>
    <td><input type="text" class="qcf-color" label="input-border" name="output-colour" value="' . esc_attr( $style['output-colour'] ) . '" /></td>
    </tr>';
    }
    $content .= '</table>';
    $content .= '<h2>Custom CSS</h2>';
    $content .= '<p>' . esc_html__( 'Use the Additional CSS option', 'quick-contact-form' ) . '&nbsp;&nbsp;&nbsp;<a class="button" href="' . admin_url( 'customize.php' ) . '">' . esc_html__( 'Additional CSS', 'quick-event-manager' ) . '</a></p>';
    $content .= '<p><input type="submit" name="Submit" class="qcf-button" value="Save Changes" />&nbsp;
<input type="submit" name="Reset" class="qcf-button" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the style settings for "' . esc_attr( $id ) . '?\' );"/></p>';
    $content .= wp_nonce_field( "save_qcf" );
    $content .= '</form>
    </div>
    <div class="qcf-options" style="float:right">
    <h2 style="color:#B52C00">Test Form</h2>
    <p>Not all of your style selections will display here (because of how WordPress works). So check the form on your site.</p>';
    $content .= qcf_loop( $id );
    $content .= '</div></div>';
    echo  $content ;
}

function qcf_reply_page( $id )
{
    qcf_change_form_update();
    
    if ( isset( $_POST['Submit'] ) && check_admin_referer( "save_qcf" ) ) {
        $options = array(
            'replytitle',
            'replyblurb',
            'replymessage',
            'replycopy',
            'replysubject',
            'fromemail',
            'messages',
            'tracker',
            'url',
            'page',
            'subject',
            'subjectoption',
            'qcf_redirect',
            'qcf_reload',
            'qcf_reload_time',
            'qcf_redirect_url',
            'qcf_bcc',
            'sendcopy',
            'copy_message',
            'bodyhead',
            'activecampaign_title',
            'createuser',
            'from_reply'
        );
        foreach ( $options as $item ) {
            
            if ( !isset( $_POST[$item] ) ) {
                $reply[$item] = '';
                continue;
            }
            
            $reply[$item] = stripslashes( $_POST[$item] );
            $reply[$item] = filter_var( $reply[$item], FILTER_SANITIZE_STRING );
            if ( $item === 'fromemail' ) {
                $reply[$item] = sanitize_email( $reply[$item] );
            }
        }
        update_option( 'qcf_reply' . $id, $reply );
        
        if ( $id ) {
            qcf_admin_notice( "The send settings for " . $id . " have been updated." );
        } else {
            qcf_admin_notice( "The default form send settings have been updated." );
        }
    
    }
    
    
    if ( isset( $_POST['Reset'] ) && check_admin_referer( "save_qcf" ) ) {
        delete_option( 'qcf_reply' . $id );
        qcf_admin_notice( "The reply settings for the form called " . $id . " have been reset." );
    }
    
    $qcf_setup = qcf_get_stored_setup();
    $id = $qcf_setup['current'];
    $reply = qcf_get_stored_reply( $id );
    $content = qcf_head_css();
    $content .= '<div class="qcf-settings"><div class="qcf-options">';
    
    if ( $id ) {
        $content .= '<h2 style="color:#B52C00">Send options for ' . $id . '</h2>';
    } else {
        $content .= '<h2 style="color:#B52C00">Default form send options</h2>';
    }
    
    $content .= qcf_change_form( $qcf_setup );
    $content .= '<form method="post" action="">
	<span class="description"><b>NOTE:</b>Leave fields blank if you don\'t want to use them</span>
    <table>
    <tr>
    <td colspan="2"><h2>Send Options</h2></td>
    </tr>
    <tr>
    <td>BCC</td>
    <td><input type="checkbox" name="qcf_bcc" ' . esc_attr( $reply['qcf_bcc'] ) . ' value="checked"> Hide email address for multiple recipients.</td>
    </tr>
    <tr>
    <td>From Address</td>
    <td>Some hosts get very picky about the senders email address. Enter a safe email if your hosts requires it:<br>
    <input type="text" name="fromemail" value="' . esc_attr( $reply['fromemail'] ) . '"/><br>
    <span class="description">If you use this feature the senders email address will automatically appear as a \'Reply To\' line in the email header.</span></td>
    </tr>
    <tr>
    <td>Email subject</td>
    <td>The message subject has two parts: the bit in the text box plus the option below.<br>
    <input style="width:100%" type="text" name="subject" value="' . esc_attr( $reply['subject'] ) . '"/><br>
    <input type="radio" name="subjectoption" value="sendername" ' . checked( $reply['subject'], 'sendername' ) . '> sender\'s name (the contents of the first field)<br />
    <input type="radio" name="subjectoption" value="sendersubj" ' . checked( $reply['subject'], 'sendersubj' ) . '> Contents of the subject field (if used)<br />
    <input type="radio" name="subjectoption" value="senderpage" ' . checked( $reply['subject'], 'senderpage' ) . '> page title (only works if sent from a post or a page)<br />
    <input type="radio" name="subjectoption" value="sendernone" ' . checked( $reply['subject'], 'sendernone' ) . '> blank
    </td>
    </tr>
    <tr>
    <td>Email body header</td>
    <td>This is the introduction to the email message you receive.<br>
    <input type="text" name="bodyhead" value="' . esc_attr( $reply['bodyhead'] ) . '"/></td>
    </tr>
    <tr>
    <td>Tracking</td>
    <td>Adds the tracking information to the message you receive.<br>
    <input style="margin:0; padding:0; border: none"type="checkbox" name="page" ' . esc_attr( $reply['page'] ) . ' value="checked"> Show page title<br />
    <input type="checkbox" name="tracker" ' . esc_attr( $reply['tracker'] ) . ' value="checked"> Show IP address<br />
    <input type="checkbox" name="url" ' . esc_attr( $reply['url'] ) . ' value="checked"> Show URL
    </td>
    </tr>
    <tr>
    <td colspan="2"><h2>Redirection</h2></td>
    </tr>
    <tr>
    <td></td>
    <td><input type="checkbox" name="qcf_redirect" ' . esc_attr( $reply['qcf_redirect'] ) . ' value="checked"> Send your visitor to new page instead of displaying the thank-you message.</td>
    </tr>
    <tr>
    <td>URL:</td>
    <td><input type="text" name="qcf_redirect_url" value="' . esc_attr( $reply['qcf_redirect_url'] ) . '"/></td>
    </tr>
    <tr>
    <td colspan="2"><h2>On Screen Thank you message</h2></td>
    </tr>
    <tr>
    <td>Thank you header</td>
    <td><input type="text" name="replytitle" value="' . esc_attr( $reply['replytitle'] ) . '"/></td>
    </tr>
    <tr><td>Thank you message</td>
    <td><textarea height: 100px" name="replyblurb">' . esc_attr( $reply['replyblurb'] ) . '</textarea></td>
    </tr>
    <tr>
    <td></td>
    <td><input type="checkbox" name="messages" ' . esc_attr( $reply['messages'] ) . ' value="checked"> Show the sender the content of their message.</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Reply Message</h2></td>
    </tr>
    <tr>
    <td  colspan="2">You can reply to the sender using the <a href="?page=quick-contact-form&tab=autoresponce">Auto Responder</a>.</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Reload Page</h2></td>
    </tr>
    <tr>
    <td></td>
    <td><input type="checkbox" name="qcf_reload" ' . esc_attr( $reply['qcf_reload'] ) . ' value="checked"> Refresh the page&nbsp;
<input style="width:2em" type="text" name="qcf_reload_time" value="' . esc_attr( $reply['qcf_reload_time'] ) . '" /> seconds after the thank-you message.</td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Create New User</h2></td>
    </tr>
    <tr>
    <td></td>
    <td><input type="checkbox" name="createuser" ' . esc_attr( $reply['createuser'] ) . ' value="checked" /> Creates a new WordPress user when the form is submitted.</td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Active Campaign Header</h2></td>
    </tr>
    <tr>
    <td colspan="2"><input type="text" name="activecampaign_title" value="' . esc_attr( $reply['activecampaign_title'] ) . '"/></td>
    </tr>
    </table>
    <p><input type="submit" name="Submit" class="qcf-button" value="Save Changes" />&nbsp;
<input type="submit" name="Reset" class="qcf-button" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the reply settings for ' . esc_attr( $id ) . '?\' );"/></p>';
    $content .= wp_nonce_field( "save_qcf" );
    $content .= '</form>
    </div>
    <div class="qcf-options" style="float:right">
    <h2 style="color:#B52C00">Test Form</h2>
    <p>Use the form below to test your thank-you message settings. You will see what your visitors see when they complete and send the form.</p>';
    $content .= qcf_loop( $id );
    $content .= '</div></div>';
    echo  $content ;
}

function qcf_error_page( $id )
{
    qcf_change_form_update();
    
    if ( isset( $_POST['Submit'] ) && check_admin_referer( "save_qcf" ) ) {
        for ( $i = 1 ;  $i <= 13 ;  $i++ ) {
            $error['field' . $i] = stripslashes( $_POST['error' . $i] );
        }
        $options = array(
            'errortitle',
            'errorblurb',
            'email',
            'telephone',
            'mathsmissing',
            'mathsanswer',
            'emailcheck',
            'phonecheck',
            'spam',
            'consent'
        );
        foreach ( $options as $item ) {
            
            if ( !isset( $_POST[$item] ) ) {
                $error[$item] = '';
                continue;
            }
            
            $error[$item] = stripslashes( $_POST[$item] );
            $error[$item] = filter_var( $error[$item], FILTER_SANITIZE_STRING );
        }
        update_option( 'qcf_error' . $id, $error );
        
        if ( $id ) {
            qcf_admin_notice( "The reply settings for " . $id . " have been updated." );
        } else {
            qcf_admin_notice( "The default form error settings have been updated." );
        }
    
    }
    
    
    if ( isset( $_POST['Reset'] ) && check_admin_referer( "save_qcf" ) ) {
        delete_option( 'qcf_error' . $id );
        qcf_admin_notice( "The error settings for the form called " . $id . " have been reset." );
    }
    
    $qcf_setup = qcf_get_stored_setup();
    $id = $qcf_setup['current'];
    $qcf = qcf_get_stored_options( $id );
    $error = qcf_get_stored_error( $id );
    $content = qcf_head_css();
    $content .= '<div class="qcf-settings"><div class="qcf-options">';
    
    if ( $id ) {
        $content .= '<h2 style="color:#B52C00">Error messages for ' . $id . '</h2>';
    } else {
        $content .= '<h2 style="color:#B52C00">Default form error messages</h2>';
    }
    
    $content .= qcf_change_form( $qcf_setup );
    $content .= '<form method="post" action="">
	<span class="description"><b>NOTE:</b> Leave fields blank if you don\'t want to use them</span>
	<table>
	<tr>
    <td colspan="2"><h2>Error Reporting</h2></td>
    </tr>
    <tr>
    <td>Error header</td>
    <td><input type="text" name="errortitle" value="' . esc_attr( $error['errortitle'] ) . '" /></td>
    </tr>
    <tr>
    <td>Error Blurb</td>
    <td><input type="text" name="errorblurb" value="' . esc_attr( $error['errorblurb'] ) . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Error Messages</h2></td>
    </tr>
    <tr>
    <td>If <em>' . esc_html( $qcf['label']['field1'] ) . '</em> is missing:</td>
    <td><input type="text" name="error1" value="' . esc_attr( $error['field1'] ) . '" /></td>
    </tr>
    <tr>
    <td>If <em>' . esc_html( $qcf['label']['field2'] ) . '</em> is missing:</td>
    <td><input type="text" name="error2" value="' . esc_attr( $error['field2'] ) . '" /></td>
    </tr>
    <tr>
    <td>Invalid email address:</td>
    <td><input type="text" name="email" value="' . esc_attr( $error['email'] ) . '" /></td>
    </tr>
    <tr>
    <td></td>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="emailcheck"' . esc_attr( $error['emailcheck'] ) . ' value="checked" /> Check for invalid email even if field is not required</td>
    </tr>
    <tr>
    <td>If <em>' . esc_html( $qcf['label']['field3'] ) . '</em> is missing:</td>
    <td><input type="text" name="error3" value="' . esc_attr( $error['field3'] ) . '" /></td>
    </tr>
    <tr>
    <td>Invalid telephone number:</td>
    <td><input type="text" name="telephone" value="' . esc_attr( $error['telephone'] ) . '" /></td>
    </tr>
    <tr>
    <td></td>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="phonecheck"' . esc_attr( $error['phonecheck'] ) . ' value="checked" /> Check for invalid phone number even if field is not required</td>
    </tr>
    <tr>
    <td>If <em>' . esc_html( $qcf['label']['field4'] ) . '</em> is missing:</td>
    <td><input type="text" name="error4" value="' . esc_attr( $error['field4'] ) . '" /></td>
    </tr>
    <tr>
    <td>Drop dopwn list:</td>
    <td><input type="text" name="error5" value="' . esc_attr( $error['field5'] ) . '" /></td>
    </tr>
    <tr>
    <td>Checkboxes:</td>
    <td><input type="text" name="error6" value="' . esc_attr( $error['field6'] ) . '" /></td>
    </tr>
    <tr>
    <td>If <em>' . esc_html( $qcf['label']['field8'] ) . '</em> is missing:</td>
    <td><input type="text" name="error8" value="' . esc_attr( $error['field8'] ) . '" /></td>
    </tr>
    <tr>
    <td>If <em>' . esc_html( $qcf['label']['field9'] ) . '</em> is missing:</td>
    <td><input type="text" name="error9" value="' . esc_attr( $error['field9'] ) . '" /></td>
    </tr>
    <tr>
    <td>If <em>' . esc_html( $qcf['label']['field10'] ) . '</em> is required:</td>
    <td><input type="text" name="error10" value="' . esc_attr( $error['field10'] ) . '" /></td>
    </tr>
    <tr>
    <td>If <em>' . esc_html( $qcf['label']['field11'] ) . '</em> is required:</td>
    <td><input type="text" name="error11" value="' . esc_attr( $error['field11'] ) . '" /></td>
    </tr>
    <tr>
    <td>If <em>' . esc_html( $qcf['label']['field13'] ) . '</em> is required:</td>
    <td><input type="text" name="error13" value="' . esc_attr( $error['field13'] ) . '" /></td>
    </tr>
    <tr>
    <td>Spam Captcha missing answer:</td>
    <td><input type="text" name="mathsmissing" value="' . esc_attr( $error['mathsmissing'] ) . '" /></td>
    </tr>
    <tr>
    <td>Spam Captcha wrong answer:</td>
    <td><input type="text" name="mathsanswer" value="' . esc_attr( $error['mathsanswer'] ) . '" /></td>
    </tr>
    <tr>
    <td>Consent is not given, but required:</td>
    <td><input type="text" name="consent" value="' . esc_attr( $error['consent'] ) . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Spam Message</h2></td>
    </tr><tr>
    <td>If spam detected:</td>
    <td><input type="text" name="spam" value="' . esc_attr( $error['spam'] ) . '" /></td>
    </tr>';
    $as_message = '<tr><td colspan="2">' . qcf_antispam_message() . '</td></tr>';
    $content .= '<tr>' . wp_kses_post( $as_message ) . '</tr>';
    $content .= '</table>
    <p><input type="submit" name="Submit" class="qcf-button" value="Save Changes" />&nbsp;
<input type="submit" name="Reset" class="qcf-button" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the error settings for ' . esc_attr( $id ) . '?\' );"/></p>';
    $content .= wp_nonce_field( "save_qcf" );
    $content .= '</form>
    </div>
    <div class="qcf-options" style="float:right"> 
    <h2 style="color:#B52C00">Error Checker</h2>
    <p>Send a blank form to test your error messages.</p>';
    $content .= qcf_loop( $id );
    $content .= '</div></div>';
    echo  $content ;
}

function qcf_antispam_message()
{
    $as_message = '
<div style="border: 1px solid black; padding: 10px; background-color: #ffcccb;">
    <p>Protect your forms from annoying spam</p> 
    <p>Simply install <a href="https://fullworks.net/products/anti-spam/" target="_blank">Fullworks\' Anti Spam</a></p>
    <p>No Recaptcha, no annoying quizes or images, simply effective. Free trial available and much cheaper than AKISMET.</p>
	</div>';
    $fs = array_key_exists( 'fwantispam_fs', $GLOBALS );
    
    if ( $fs ) {
        global  $fwantispam_fs ;
        if ( $fwantispam_fs->can_use_premium_code() ) {
            $as_message = '<h2>Anti Spam Protection</h2>
<div style="border: 1px solid black; padding: 10px; background-color: #90ee90;">
    <p>Brilliant - you are automatically protect from spam</p> 
    <p>By <a href="' . esc_url( get_admin_url() ) . 'options-general.php?page=fullworks-anti-spam-settings" >Fullworks\' Anti Spam - see the settings here</a> </p>
	<p><a href="?page=quick-contact-form&tab=error">Change the anti-spam error message</a>.</p></div>';
        }
    }
    
    return $as_message;
}

// Build Mailing List
function qcf_buildlist_page()
{
    /**
     * @var \Freemius $quick_contact_form_fs Object for freemius.
     */
    global  $quick_contact_form_fs ;
    $content = '<div class="qcf-settings"><div class="qcf-options">';
    $content .= '<p>' . esc_html__( 'To build a mailing list', 'quick-contact-form' ) . ' <a href="?page=quick-contact-form&tab=extensions">' . esc_html__( 'Upgrade to Pro', 'quick-contact-form' ) . '</a>.</p>';
    $content .= '</div></div>';
    echo  $content ;
}

function qcf_autoresponce_page( $id )
{
    qcf_change_form_update();
    
    if ( isset( $_POST['Submit'] ) && check_admin_referer( "save_qcf" ) ) {
        $options = array(
            'enable',
            'subject',
            'message',
            'fromname',
            'fromemail',
            'sendcopy'
        );
        foreach ( $options as $item ) {
            
            if ( !isset( $_POST[$item] ) ) {
                $auto[$item] = '';
                continue;
            }
            
            $auto[$item] = stripslashes( wp_kses_post( $_POST[$item] ) );
        }
        update_option( 'qcf_autoresponder' . $id, $auto );
        
        if ( $id ) {
            qcf_admin_notice( "The autoresponder settings for " . $id . " have been updated." );
        } else {
            qcf_admin_notice( "The default form autoresponder settings have been updated." );
        }
    
    }
    
    
    if ( isset( $_POST['Reset'] ) && check_admin_referer( "save_qcf" ) ) {
        delete_option( 'qcf_autoresponder' . $id );
        qcf_admin_notice( "The autoresponder settings for the form called " . $id . " have been reset." );
    }
    
    $qcf_setup = qcf_get_stored_setup();
    $id = $qcf_setup['current'];
    $qcf = qcf_get_stored_options( $id );
    $auto = qcf_get_stored_autoresponder( $id );
    $message = $auto['message'];
    $content = qcf_head_css();
    $content .= '<div class="qcf-settings"><div class="qcf-options" style="width:90%;">';
    
    if ( $id ) {
        $content .= '<h2 style="color:#B52C00">Autoresponse settings for ' . $id . '</h2>';
    } else {
        $content .= '<h2 style="color:#B52C00">Default form autoresponse settings</h2>';
    }
    
    $content .= qcf_change_form( $qcf_setup );
    $content .= '<p>The auto responder allows you to format a proper HTML message with media, links and so on.</p>
    <form method="post" action="">
	<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="enable"' . esc_attr( $auto['enable'] ) . ' value="checked" /> Enable Auto Responder.</p>
<p>From Name (<span class="description">Defaults to your <a href="' . get_admin_url() . 'options-general.php">Site Title</a> if left blank.</span>):<br>
    <input type="text" style="width:50%" name="fromname" value="' . esc_attr( $auto['fromname'] ) . '" /></p>
    <p>From Email (<span class="description">Defaults to the <a href="?page=quick-contact-form&tab=setup">Setup Email</a> if left blank.</span>):<br>
    <input type="text" style="width:50%" name="fromemail" value="' . esc_attr( $auto['fromemail'] ) . '" /></p>    
<p>Subject:<br>
<input style="width:100%" type="text" name="subject" value="' . esc_attr( $auto['subject'] ) . '"/></p>
    <h2>Message Content</h2>';
    echo  $content ;
    wp_editor( $message, 'message', $settings = array(
        'textarea_rows' => '20',
        'wpautop'       => false,
    ) );
    $content = '<p>You can use the following shortcodes in the message body:</p>
    <table>
    <tr>
    <th>Shortcode</th>
    <th>Replacement Text</th>
    </tr>
    <tr>
    <td>[name]</td>
    <td>The persons name as filled in on the form.</td>
    </tr>
    <tr>
    <td>[date]</td>
    <td>The date selected on the form.</td>
    </tr>
    <tr>
    <td>[option]</td>
    <td>The option chosen from the first selector field.</td>
    </tr>
    </table>
    <p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="sendcopy"' . esc_attr( $auto['sendcopy'] ) . ' value="checked" /> Add senders message content to the email</p>
    <p><input type="submit" name="Submit" class="qcf-button" value="Save Changes" />&nbsp;
<input type="submit" name="Reset" class="qcf-button" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the error settings for "' . esc_attr( $id ) . '?\' );"/></p>';
    $content .= wp_nonce_field( "save_qcf" );
    $content .= '</form>
    </div>
    </div>';
    echo  $content ;
}

// Upgrade
function qcf_delete_everything()
{
    $qcf_setup = qcf_get_stored_setup();
    $arr = explode( ",", $qcf_setup['alternative'] );
    foreach ( $arr as $item ) {
        qcf_delete_things( $item );
    }
    delete_option( 'qcf_setup' );
    delete_option( 'qcf_email' );
    delete_option( 'qcf_message' );
}

function qcf_delete_things( $id )
{
    delete_option( 'qcf_settings' . $id );
    delete_option( 'qcf_reply' . $id );
    delete_option( 'qcf_error' . $id );
    delete_option( 'qcf_style' . $id );
    delete_option( 'qcf_attach' . $id );
}

function qcf_admin_notice( $message = '' )
{
    if ( !empty($message) ) {
        echo  '<div class="updated"><p>' . $message . '</p></div>' ;
    }
}

function qcf_change_form( $qcf_setup )
{
    $content = '';
    
    if ( $qcf_setup['alternative'] ) {
        $content = '<form style="margin-top: 8px" method="post" action="" >';
        $arr = explode( ",", $qcf_setup['alternative'] );
        sort( $arr );
        foreach ( $arr as $item ) {
            
            if ( $qcf_setup['current'] == $item ) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            
            
            if ( $item == '' ) {
                $formname = 'default';
                $item = '';
            } else {
                $formname = $item;
            }
            
            $content .= '<input type="radio" name="current" value="' . esc_attr( $item ) . '" ' . esc_attr( $checked ) . ' />' . esc_html( $formname ) . ' ';
        }
        $content .= '<input type="hidden" name="alternative" value = "' . esc_attr( $qcf_setup['alternative'] ) . '" />
        &nbsp;&nbsp;
        <input type="submit" name="Select" class="qcf-button" value="Change Form" /></form>';
    }
    
    return $content;
}

function qcf_change_form_update()
{
    
    if ( isset( $_POST['Select'] ) ) {
        $qcf_setup['current'] = sanitize_text_field( $_POST['current'] );
        $qcf_setup['alternative'] = sanitize_text_field( $_POST['alternative'] );
        update_option( 'qcf_setup', $qcf_setup );
    }

}

function qcf_generate_csv()
{
    
    if ( isset( $_POST['download_csv'] ) ) {
        if ( !isset( $_POST['_qcf_messages_download_nonce'] ) || !wp_verify_nonce( $_POST['_qcf_messages_download_nonce'], 'qcf_messages_download' ) ) {
            wp_die( esc_html__( 'Nonce validation - security failed', 'quick-contact-form' ) );
        }
        $id = sanitize_text_field( $_POST['formname'] );
        $filename = rawurlencode( $id . '.csv' );
        if ( $id == '' ) {
            $filename = rawurlencode( 'default.csv' );
        }
        header( 'Content-Description: File Transfer' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Type: text/csv' );
        $outstream = fopen( "php://output", 'w' );
        $message = get_option( 'qcf_messages' . $id, array() );
        $qcf = qcf_get_stored_options( $id );
        $headerrow = array();
        foreach ( explode( ',', $qcf['sort'] ) as $name ) {
            if ( $qcf['active_buttons'][$name] == "on" && $name != 'field12' ) {
                array_push( $headerrow, $qcf['label'][$name] );
            }
        }
        array_push( $headerrow, 'Date Sent' );
        fputcsv(
            $outstream,
            $headerrow,
            ',',
            '"'
        );
        foreach ( array_reverse( $message ) as $value ) {
            $cells = array();
            foreach ( explode( ',', $qcf['sort'] ) as $name ) {
                if ( $qcf['active_buttons'][$name] == "on" && $name != 'field12' ) {
                    array_push( $cells, $value[$name] );
                }
            }
            array_push( $cells, $value['field0'] );
            fputcsv(
                $outstream,
                $cells,
                ',',
                '"'
            );
        }
        fclose( $outstream );
        exit;
    }

}

function qcf_admin_pages()
{
    $qcf_setup = qcf_get_stored_setup();
    if ( !$qcf_setup['nostore'] ) {
        add_menu_page(
            'Messages',
            'Messages',
            'manage_options',
            'quick-contact-form-messages',
            function () {
            require_once 'messages.php';
        },
            'dashicons-email-alt'
        );
    }
}

function qcf_page_init()
{
    add_options_page(
        'Quick Contact',
        'Quick Contact',
        'manage_options',
        QUICK_CONTACT_FORM_PLUGIN_NAME,
        'qcf_tabbed_page'
    );
}

function qcf_settings_init()
{
    qcf_generate_csv();
    return;
}

function qcf_admin_style_scripts()
{
    qcf_style_scripts();
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_style( 'qcf_settings', plugins_url( 'settings.css', __FILE__ ) );
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_media();
    wp_enqueue_script(
        'qcf-media',
        plugins_url( 'media.js', __FILE__ ),
        array( 'jquery', 'wp-color-picker' ),
        false,
        true
    );
}

add_action(
    'plugin_row_meta',
    'qcf_plugin_row_meta',
    10,
    2
);
function qcf_plugin_row_meta( $links, $file = '' )
{
    global  $quick_contact_form_fs ;
    
    if ( false !== strpos( $file, '/quick-contact-form.php' ) ) {
        $new_links[] = '<a href="https://fullworks.net/docs/quick-contact-form/"><strong>' . esc_html__( 'Help and Support', 'quick-contact-form' ) . '</strong></a>';
        
        if ( $quick_contact_form_fs->is_not_paying() ) {
            global  $quick_contact_form_fs ;
            
            if ( $quick_contact_form_fs->is_trial() || $quick_contact_form_fs->is_trial_utilized() ) {
                $upurl = $quick_contact_form_fs->get_upgrade_url();
                $upmsg = esc_html__( 'Upgrade to Pro', 'quick-contact-form' );
                $new_links[] = '<a href="' . $upurl . '"><strong>' . $upmsg . '</strong></a>';
            } else {
                $upurl = $quick_contact_form_fs->get_trial_url();
                $upmsg = esc_html__( 'Pro: Free 14 Day Trial', 'quick-contact-form' );
                $new_links[] = '<a href="' . $upurl . '"><strong>' . $upmsg . '</strong></a>';
            }
            
            $links = array_merge( $links, $new_links );
        }
    
    }
    
    return $links;
}

function qcf_clone( $id, $clone )
{
    if ( $clone == 'default' ) {
        $clone = '';
    }
    $update = qcf_get_stored_options( $clone );
    update_option( 'qcf_settings' . $id, $update );
    $update = qcf_get_stored_attach( $clone );
    update_option( 'qcf_attach' . $id, $update );
    $update = qcf_get_stored_style( $clone );
    update_option( 'qcf_style' . $id, $update );
    $update = qcf_get_stored_reply( $clone );
    update_option( 'qcf_reply' . $id, $update );
    $update = qcf_get_stored_error( $clone );
    update_option( 'qcf_error' . $id, $update );
    $update = qcf_get_stored_autoresponder( $clone );
    update_option( 'qcf_autoresponder' . $id, $update );
}

function qcf_tabbed_page()
{
    global  $quick_contact_form_fs ;
    $qcf_setup = qcf_get_stored_setup();
    $id = $qcf_setup['current'];
    echo  '<div class="wrapper"><h1>Quick Contact Form</h1>' ;
    
    if ( isset( $_GET['tab'] ) ) {
        qcf_admin_tabs( $_GET['tab'] );
        $tab = $_GET['tab'];
    } else {
        qcf_admin_tabs( 'setup' );
        $tab = 'setup';
    }
    
    switch ( $tab ) {
        case 'setup':
            qcf_setup( $id );
            break;
        case 'settings':
            qcf_form_settings( $id );
            break;
        case 'styles':
            qcf_styles( $id );
            break;
        case 'reply':
            qcf_reply_page( $id );
            break;
        case 'error':
            qcf_error_page( $id );
            break;
        case 'attach':
            qcf_attach( $id );
            break;
        case 'help':
            qcf_help( $id );
            break;
        case 'reset':
            qcf_reset_page( $id );
            break;
        case 'autoresponce':
            qcf_autoresponce_page( $id );
            break;
        case 'sendemail':
            qcf_sendtolist_page__premium_only( null );
            break;
        case 'buildlist':
            qcf_buildlist_page();
            break;
        case 'extensions':
            break;
    }
    echo  '</div>' ;
}

function qcf_admin_tabs( $current = 'settings' )
{
    $tabs = array(
        'setup'        => 'Setup',
        'settings'     => 'Form Settings',
        'attach'       => 'Attachments',
        'styles'       => 'Styling',
        'reply'        => 'Send Options',
        'autoresponce' => 'Auto Responder',
        'error'        => 'Error Messages',
        'extensions'   => 'Extensions',
    );
    $links = array();
    echo  '<h2 class="nav-tab-wrapper">' ;
    foreach ( $tabs as $tab => $name ) {
        $class = ( $tab == $current ? ' nav-tab-active' : '' );
        echo  '<a class="nav-tab' . $class . '" href="?page=' . QUICK_CONTACT_FORM_PLUGIN_NAME . '&tab=' . $tab . '">' . $name . '</a>' ;
    }
    echo  '</h2>' ;
}

add_action( 'init', 'qcf_settings_init' );
add_action( 'admin_menu', 'qcf_page_init' );
add_action( 'admin_menu', 'qcf_admin_pages' );
add_action( 'admin_enqueue_scripts', 'qcf_admin_style_scripts' );
// make sure customizer is there even for FSE themes
add_action(
    'customize_register',
    function ( $manager ) {
},
    10,
    1
);
function qcf_kses_settings( $html )
{
    $kses_defaults = wp_kses_allowed_html( 'post' );
    $svg_args = array(
        'form'     => array(
        'class' => true,
    ),
        'select'   => array(
        'class' => true,
        'name'  => true,
        'style' => true,
    ),
        'option'   => array(
        'class' => true,
        'value' => true,
        'style' => true,
    ),
        'input'    => array(
        'class' => true,
        'name'  => true,
        'type'  => true,
        'value' => true,
        'style' => true,
    ),
        'textarea' => array(
        'class' => true,
        'name'  => true,
        'type'  => true,
        'value' => true,
        'style' => true,
    ),
    );
    $allowed_tags = array_merge( $kses_defaults, $svg_args );
    return wp_kses( $html, $allowed_tags );
}
