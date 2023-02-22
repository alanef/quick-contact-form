<?php

/**
 * @var \Freemius $quick_contact_form_fs Object for freemius.
 */
global  $quick_contact_form_fs ;
add_action( 'wp_ajax_qcf_validate_form', 'qcf_validate_form_callback' );
add_action( 'wp_ajax_nopriv_qcf_validate_form', 'qcf_validate_form_callback' );
require_once plugin_dir_path( __FILE__ ) . 'options.php';
require_once plugin_dir_path( __FILE__ ) . 'akismet.php';
if ( is_admin() ) {
    require_once plugin_dir_path( __FILE__ ) . '/settings.php';
}
add_shortcode( 'qcf', 'qcf_start' );
add_filter(
    'plugin_action_links',
    'qcf_plugin_action_links',
    10,
    2
);
add_action( 'wp_enqueue_scripts', 'qcf_style_scripts', 99 );
add_action( 'widgets_init', 'add_qcf_widget' );
function add_qcf_widget()
{
    register_widget( 'qcf_widget' );
}

function qcf_block_init()
{
    if ( !function_exists( 'register_block_type' ) ) {
        return;
    }
    wp_register_script( 'qcf_block', plugins_url( 'block.js', __FILE__ ), array(
        'wp-blocks',
        'wp-element',
        'wp-components',
        'wp-editor'
    ) );
    register_block_type( 'quick-contact-form/block', array(
        'editor_script'   => 'qcf_block',
        'render_callback' => 'qcf_loop',
    ) );
}

add_action( 'init', 'qcf_block_init' );
function qcf_display_form( $values, $errors, $id )
{
    /**
     * @var \Freemius $quick_contact_form_fs Object for freemius.
     */
    global  $quick_contact_form_fs ;
    $qcf_form = qcf_get_stored_setup();
    $qcf = qcf_get_stored_options( $id );
    $error = qcf_get_stored_error( $id );
    $reply = qcf_get_stored_reply( $id );
    $attach = qcf_get_stored_attach( $id );
    $style = qcf_get_stored_style( $id );
    $qcf['required']['field12'] = 'checked';
    $hd = ( $style['header-type'] ? $style['header-type'] : 'h2' );
    $content = '';
    
    if ( $id ) {
        $formstyle = $id;
    } else {
        $formstyle = 'default';
    }
    
    if ( !empty($qcf['title']) ) {
        $qcf['title'] = '<' . $hd . ' class="qcf-header">' . $qcf['title'] . '</' . $hd . '>';
    }
    if ( !empty($qcf['blurb']) ) {
        $qcf['blurb'] = '<p class="qcf-blurb">' . $qcf['blurb'] . '</p>';
    }
    if ( !empty($qcf['mathscaption']) ) {
        $qcf['mathscaption'] = '<p class="input">' . $qcf['mathscaption'] . '</p>';
    }
    if ( isset( $errors['spam'] ) && $errors['spam'] ) {
        $error['errorblurb'] = $errors['spam'];
    }
    if ( count( $errors ) > 0 ) {
        $content = "<a id='qcf_reload'></a>";
    }
    $content .= '<div class="qcf-main qcf-style ' . $formstyle . '"><div id="' . $style['border'] . '">';
    $content .= '<div class="qcf-state qcf-ajax-loading qcf-style ' . $formstyle . '">' . apply_filters( 'qcf_validating_h2_markup', '<' . $hd . ' class="validating">' ) . $error['validating'] . apply_filters( 'qcf_validating_end_h2_markup', '</' . $hd . '>' ) . '</div>';
    $content .= '<div class="qcf-state qcf-ajax-error qcf-style ' . $formstyle . '"><div align="center">Ouch! There was a server error.<br /><a class="qcf-retry">Retry &raquo;</a></div></div>';
    $content .= '<div class="qcf-state qcf-sending qcf-style ' . $formstyle . '">' . apply_filters( 'qcf_sending_h2_markup', '<' . $hd . ' class="sending">' ) . $error['sending'] . apply_filters( 'qcf_sending_end_h2_markup', '</' . $hd . '>' ) . '</div>';
    $content .= "<div class='qcf-state qcf-form-wrapper'>\r\t";
    //  $content .= "<div id='" . $style['border'] . "'>\r\t";
    
    if ( count( $errors ) > 0 ) {
        $content .= "<" . $hd . " class='error'>" . $error['errortitle'] . "</" . $hd . ">\r\t<p class='error'>" . $error['errorblurb'] . "</p>\r\t";
    } else {
        $content .= $qcf['title'] . "\r\t" . $qcf['blurb'] . "\r\t";
    }
    
    $name = ( empty($id) ? 'default' : esc_attr( $id ) );
    $content .= "<form class='qcf-form' action=\"\" method=\"POST\" enctype=\"multipart/form-data\" id=\"" . wp_unique_id( 'qfc-form-' . esc_attr( $name ) . '-' ) . "\">\r\t";
    $content .= "<input type='hidden' name='id' value='{$id}' />\r\t";
    foreach ( explode( ',', $qcf['sort'] ) as $name ) {
        $required = ( $qcf['required'][$name] ? 'required' : '' );
        if ( $qcf['active_buttons'][$name] == "on" ) {
            switch ( $name ) {
                case 'field1':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname1', $content );
                    $content .= '<input type="text" id="qcf-form-field-id-' . $id . '-1" placeholder="' . $values['qcfname1'] . '" class="qcf-form-field' . $required . '" name="qcfname1" value="" >' . "\r\t";
                    break;
                case 'field2':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname2', $content );
                    $content .= '<input type="email" id="qcf-form-field-id-' . $id . '-2" placeholder="' . $values['qcfname2'] . '" class="qcf-form-field' . $required . '" name="qcfname2"  value="" >' . "\r\t";
                    break;
                case 'field3':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname3', $content );
                    $content .= '<input type="text" id="qcf-form-field-id-' . $id . '-3" placeholder="' . $values['qcfname3'] . '" class="qcf-form-field' . $required . '" name="qcfname3"  value="" >' . "\r\t";
                    break;
                case 'field4':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname4', $content );
                    $content .= '<textarea id="qcf-form-field-id-' . $id . '-4" placeholder="' . $values['qcfname4'] . '" class="qcf-form-field' . $required . '"  rows="' . $qcf['lines'] . '" name="qcfname4"></textarea>' . "\r\t";
                    break;
                case 'field5':
                    if ( isset( $errors['qcfname5'] ) && $errors['qcfname5'] ) {
                        $required = 'error';
                    }
                    if ( $qcf['selectora'] == 'dropdowna' ) {
                        $content .= qcf_dropdown(
                            'qcfname5',
                            'dropdownlist',
                            $values,
                            $errors,
                            $required,
                            $qcf,
                            $name
                        );
                    }
                    if ( $qcf['selectora'] == 'checkboxa' ) {
                        $content .= qcf_checklist(
                            'qcfname5',
                            'dropdownlist',
                            $values,
                            $errors,
                            $required,
                            $qcf,
                            $name
                        );
                    }
                    if ( $qcf['selectora'] == 'radioa' ) {
                        $content .= qcf_radio(
                            'qcfname5',
                            'dropdownlist',
                            $values,
                            $errors,
                            $required,
                            $qcf,
                            $name
                        );
                    }
                    break;
                case 'field6':
                    if ( isset( $errors['qcfname6'] ) && $errors['qcfname6'] ) {
                        $required = 'error';
                    }
                    if ( $qcf['selectorb'] == 'dropdownb' ) {
                        $content .= qcf_dropdown(
                            'qcfname6',
                            'checklist',
                            $values,
                            $errors,
                            $required,
                            $qcf,
                            $name
                        );
                    }
                    if ( $qcf['selectorb'] == 'checkboxb' ) {
                        $content .= qcf_checklist(
                            'qcfname6',
                            'checklist',
                            $values,
                            $errors,
                            $required,
                            $qcf,
                            $name
                        );
                    }
                    if ( $qcf['selectorb'] == 'radiob' ) {
                        $content .= qcf_radio(
                            'qcfname6',
                            'checklist',
                            $values,
                            $errors,
                            $required,
                            $qcf,
                            $name
                        );
                    }
                    break;
                case 'field7':
                    if ( isset( $errors['qcfname7'] ) && $errors['qcfname7'] ) {
                        $required = 'error';
                    }
                    if ( $qcf['selectorc'] == 'dropdownc' ) {
                        $content .= qcf_dropdown(
                            'qcfname7',
                            'radiolist',
                            $values,
                            $errors,
                            $required,
                            $qcf,
                            $name
                        );
                    }
                    if ( $qcf['selectorc'] == 'checkboxc' ) {
                        $content .= qcf_checklist(
                            'qcfname7',
                            'radiolist',
                            $values,
                            $errors,
                            $required,
                            $qcf,
                            $name
                        );
                    }
                    if ( $qcf['selectorc'] == 'radioc' ) {
                        $content .= qcf_radio(
                            'qcfname7',
                            'radiolist',
                            $values,
                            $errors,
                            $required,
                            $qcf,
                            $name
                        );
                    }
                    break;
                case 'field8':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname8', $content );
                    $content .= '<input type="text"  id="qcf-form-field-id-' . $id . '-8" placeholder="' . $values['qcfname8'] . '" class="qcf-form-field' . $required . '" name="qcfname8"  value=""  >' . "\r\t";
                    break;
                case 'field9':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname9', $content );
                    $content .= '<input type="text" id="qcf-form-field-id-' . $id . '-9" placeholder="' . $values['qcfname9'] . '" class="qcf-form-field' . $required . '" name="qcfname9"  value="" >' . "\r\t";
                    break;
                case 'field10':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname10', $content );
                    $content .= '<input type="text" id="qcf-form-field-id-' . $id . '-10" placeholder="' . $values['qcfname10'] . '" class="qcf-form-field qcfdate ' . $required . '" name="qcfname10"  value=""  />';
                    break;
                case 'field11':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname11', $content );
                    
                    if ( $qcf['fieldtype'] == 'tdate' ) {
                        $content .= '<input type="text" id="qcf-form-field-id-' . $id . '-11" placeholder="' . $values['qcfname11'] . '" class="qcf-form-field qcfdate ' . $required . '" name="qcfname11"  value=""  /></p>';
                    } else {
                        $content .= '<input type="text" id="qcf-form-field-id-' . $id . '-11" placeholder="' . $values['qcfname11'] . '" class="qcf-form-field' . $required . '" label="Multibox 1" name="qcfname11" value="" ><br>' . "\r\t";
                    }
                    
                    break;
                case 'field13':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname13', $content );
                    
                    if ( $qcf['fieldtypeb'] == 'bdate' ) {
                        $content .= '<input type="text" id="qcf-form-field-id-' . $id . '-13" placeholder="' . $values['qcfname13'] . '" class="qcf-form-field qcfdate ' . $required . '" name="qcfname13"  value="" >';
                    } else {
                        $content .= '<input type="text" id="qcf-form-field-id-' . $id . '-13" placeholder="' . $values['qcfname13'] . '" class="qcf-form-field' . $required . '" name="qcfname13" value="" >' . "\r\t";
                    }
                    
                    break;
                case 'field12':
                    if ( isset( $errors['qcfname12'] ) && $errors['qcfname12'] ) {
                        $required = 'error';
                    }
                    
                    if ( $errors['qcfname12'] ) {
                        $content .= $errors['qcfname12'];
                    } else {
                        $content .= '<p>' . $qcf['label']['field12'] . '</p>';
                    }
                    
                    $content .= '<p>' . strip_tags( $values['thesum'] ) . ' = <input type="text" class="' . $required . '" style="width:3em;font-size:inherit;" name="qcfname12"  value="' . strip_tags( $values['qcfname12'] ) . '"></p> 
                <input type="hidden" name="answer" value="' . strip_tags( $values['answer'] ) . '" />
                <input type="hidden" name="thesum" value="' . strip_tags( $values['thesum'] ) . '" />';
                    break;
                case 'field14':
                    $content .= '<p>' . $qcf['label']['field14'] . '</p>
                <input type="range" class="qcf-range-slider-control" name="qcfname14" min="' . $qcf['min'] . '" max="' . $qcf['max'] . '" value="' . $qcf['initial'] . '" step="' . $qcf['step'] . '" data-rangeslider>
                <div class="qcf-slideroutput">';
                    
                    if ( $qcf['output-values'] ) {
                        $content .= '<span class="qcf-sliderleft">' . $qcf['min'] . '</span>
                    <span class="qcf-slidercenter"><output></output></span>
                    <span class="qcf-sliderright">' . $qcf['max'] . '</span>';
                    } else {
                        $content .= '<span class="qcf-outputcenter"><output></output></span>';
                    }
                    
                    $content .= '</div><div style="clear: both;"></div>';
                    break;
                case 'field15':
                    list( $required, $content ) = qcf_form_field_error( $errors, 'qcfname15', $content );
                    $content .= '<input type="checkbox" name="qcfname15"  value="checked"  />&nbsp;' . $qcf['label']['field15'] . "\r\t";
                    break;
            }
        }
    }
    
    if ( $attach['qcf_attach'] == "checked" ) {
        /*
        	@Change
        	@Add <div> around file inputs
        */
        $content .= '<div>';
        /*
        	@Change
        	@Add <script> block with a simple file info object
        */
        $qfc_file_info = (object) array(
            'required'       => (int) (( $attach['qcf_required'] == "checked" ? 1 : 0 )),
            'types'          => explode( ',', $attach['qcf_attach_type'] ),
            'max_size'       => (int) $attach['qcf_attach_size'],
            'error'          => $attach['qcf_attach_error'],
            'error_size'     => $attach['qcf_attach_error_size'],
            'error_type'     => $attach['qcf_attach_error_type'],
            'error_required' => $attach['qcf_attach_error_required'],
        );
        $script = 'qfc_file_info = ' . json_encode( $qfc_file_info ) . ';';
        wp_add_inline_script( 'qcf_script', $script, 'before' );
        
        if ( $errors['attach'] ) {
            $content .= $errors['attach'];
        } else {
            $content .= '<p class="input">' . $attach['qcf_attach_label'] . '</p>' . "\r\t" . '<p>';
        }
        
        $size = $attach['qcf_attach_width'];
        $content .= '<div name="attach">';
        for ( $i = 1 ;  $i < $attach['qcf_number'] ;  $i++ ) {
            $content .= '<input type="file" size="' . $size . '" name="filename' . $i . '" class="qcf_filename_input qcf_filename' . $i . '"/><br>';
        }
        $content .= '<input type="file" size="' . $size . '" name="filename' . $attach['qcf_number'] . '" class="qcf_filename_input qcf_filename' . $i . '"/></p>';
        $content .= '</div></div>';
    }
    
    $caption = $qcf['send'];
    
    if ( $style['submit-button'] ) {
        $content .= '<p><input type="image" value="' . $caption . '" src="' . $style['submit-button'] . '" id="submit" name="qcfsubmit' . $id . '" /></p>';
    } else {
        $content .= '<p><input type="submit" value="' . $caption . '" id="submit" name="qcfsubmit' . $id . '" /></p>';
    }
    
    $content .= '<input type="hidden" name="form_id" value="' . uniqid() . wp_unique_id() . '" />';
    $content .= '</form></div>' . "\r\t" . '<div style="clear:both;"></div></div>' . "\r\t" . '</div>' . "\r\t";
    // close
    if ( count( $errors ) > 0 ) {
        wp_add_inline_script( 'qcf_script', 'document.getElementById("qcf_reload").scrollIntoView();' );
    }
    echo  qcf_kses_forms( $content ) ;
}

/**
 * @param $errors
 * @param $key
 * @param $content
 *
 * @return array
 */
function qcf_form_field_error( $errors, $key, $content )
{
    $required = '';
    if ( isset( $errors[$key] ) && $errors[$key] ) {
        $required = 'error';
    }
    if ( isset( $errors[$key] ) ) {
        $content .= $errors[$key];
    }
    return array( $required, $content );
}

function qcf_dropdown(
    $var,
    $list,
    $values,
    $errors,
    $required,
    $qcf,
    $name
)
{
    $content = $errors[$var];
    $content .= '<select name="' . $var . '" class="' . $required . '" ><option value="' . $qcf['label'][$name] . '">' . $qcf['label'][$name] . '</option>' . "\r\t";
    $arr = explode( ",", $qcf[$list] );
    foreach ( $arr as $item ) {
        $selected = '';
        if ( $values[$var] == $item ) {
            $selected = 'selected';
        }
        $content .= '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>' . "\r\t";
    }
    $content .= '</select>' . "\r\t";
    return $content;
}

function qcf_checklist(
    $var,
    $list,
    $values,
    $errors,
    $required,
    $qcf,
    $name
)
{
    
    if ( $errors[$var] ) {
        $content = $errors[$var];
    } else {
        $content = '<p class="input ' . $required . '">' . $qcf['label'][$name] . '</p>';
    }
    
    $content .= '<p class="input">';
    $arr = explode( ",", $qcf[$list] );
    foreach ( $arr as $item ) {
        $checked = '';
        if ( $values[$var . '_' . str_replace( ' ', '', $item )] == $item ) {
            $checked = 'checked';
        }
        $content .= '<label><input type="checkbox" style="margin:0; padding: 0; border: none" name="' . $var . '_' . str_replace( ' ', '', $item ) . '" value="' . $item . '" ' . $checked . '> ' . $item . '</label><br>';
    }
    $content .= '</p>';
    return $content;
}

function qcf_radio(
    $var,
    $list,
    $values,
    $errors,
    $required,
    $qcf,
    $name
)
{
    $content = '<p class="input">' . $qcf['label'][$name] . '</p>';
    $arr = explode( ",", $qcf[$list] );
    foreach ( $arr as $item ) {
        $checked = '';
        if ( $values[$var] == $item ) {
            $checked = 'checked';
        }
        
        if ( $item === reset( $arr ) ) {
            $content .= '<p class="input"><input type="radio" style="margin:0; padding: 0; border: none" name="' . $var . '" value="' . $item . '" id="' . $item . '" checked><label for="' . $item . '"> ' . $item . '</label><br>';
        } else {
            $content .= '<p class="input"><input type="radio" style="margin:0; padding: 0; border: none" name="' . $var . '" value="' . $item . '" id="' . $item . '" ' . $checked . '><label for="' . $item . '"> ' . $item . '</label><br>';
        }
    
    }
    $content .= '</p>';
    return $content;
}

function qcf_verify_form( &$values, &$errors, $id )
{
    $qcf = qcf_get_stored_options( $id );
    $error = qcf_get_stored_error( $id );
    $attach = qcf_get_stored_attach( $id );
    $apikey = get_option( 'qcf_akismet' );
    
    if ( $apikey ) {
        $blogurl = get_site_url();
        $akismet = new qcf_akismet( $blogurl, $apikey );
        $akismet->setCommentAuthor( $values['qcfname1'] );
        $akismet->setCommentAuthorEmail( $values['qcfname2'] );
        $akismet->setCommentContent( $values['qcfname4'] );
        if ( $akismet->isCommentSpam() ) {
            $errors['spam'] = $error['spam'];
        }
    }
    
    $errors = apply_filters(
        'quick_entry_is_spam',
        $errors,
        $values['qcfname1'],
        $values['qcfname2'],
        $values['qcfname4'],
        $values,
        $error['spam']
    );
    $emailcheck = $error['emailcheck'];
    if ( $qcf['required']['field2'] == 'checked' ) {
        $emailcheck = 'checked';
    }
    $qcf['required']['field12'] = 'checked';
    $phonecheck = $error['phonecheck'];
    if ( $qcf['required']['field3'] == 'checked' ) {
        $phonecheck = 'checked';
    }
    if ( $qcf['active_buttons']['field2'] && $emailcheck && $values['qcfname2'] !== $qcf['label']['field2'] ) {
        if ( !filter_var( $values['qcfname2'], FILTER_VALIDATE_EMAIL ) ) {
            $errors['qcfname2'] = '<p class="qcf-input-error"><span>' . $error['email'] . '</span></p>';
        }
    }
    if ( $qcf['active_buttons']['field3'] && $phonecheck == 'checked' && $values['qcfname3'] !== $qcf['label']['field3'] ) {
        if ( preg_match( "/[^0-9()\\+\\.\\-\\s]\$/", $values['qcfname3'] ) ) {
            $errors['qcfname3'] = '<p class="qcf-input-error"><span>' . $error['telephone'] . '</span></p>';
        }
    }
    if ( $qcf['fieldtype'] == 'tmail' && $qcf['active_buttons']['field11'] && $values['qcfname11'] !== $qcf['label']['field11'] ) {
        if ( !filter_var( $values['qcfname11'], FILTER_VALIDATE_EMAIL ) ) {
            $errors['qcfname11'] = '<p class="qcf-input-error"><span>' . $error['email'] . '</span></p>';
        }
    }
    if ( $qcf['fieldtype'] == 'ttele' && $qcf['active_buttons']['field11'] && $phonecheck == 'checked' && $values['qcfname11'] !== $qcf['label']['field11'] ) {
        if ( preg_match( "/[^0-9()\\+\\.\\-\\s]\$/", $values['qcfname11'] ) ) {
            $errors['qcfname11'] = '<p class="qcf-input-error"><span>' . $error['telephone'] . '</span></p>';
        }
    }
    if ( $qcf['fieldtypeb'] == 'bmail' && $qcf['active_buttons']['field13'] && $values['qcfname13'] !== $qcf['label']['field13'] ) {
        if ( !filter_var( $values['qcfname13'], FILTER_VALIDATE_EMAIL ) ) {
            $errors['qcfname13'] = '<p class="qcf-input-error"><span>' . $error['email'] . '</span></p>';
        }
    }
    if ( $qcf['fieldtypeb'] == 'btele' && $qcf['active_buttons']['field13'] && $phonecheck == 'checked' && $values['qcfname13'] !== $qcf['label']['field13'] ) {
        if ( preg_match( "/[^0-9()\\+\\.\\-\\s]\$/", $values['qcfname11'] ) ) {
            $errors['qcfname13'] = '<p class="qcf-input-error"><span>' . $error['telephone'] . '</span></p>';
        }
    }
    foreach ( explode( ',', $qcf['sort'] ) as $name ) {
        if ( $qcf['active_buttons'][$name] && $qcf['required'][$name] ) {
            switch ( $name ) {
                case 'field1':
                    $values['qcfname1'] = filter_var( $values['qcfname1'], FILTER_SANITIZE_STRING );
                    if ( empty($values['qcfname1']) || $values['qcfname1'] == $qcf['label'][$name] ) {
                        $errors['qcfname1'] = '<p class="qcf-input-error"><span>' . $error['field1'] . '</span></p>';
                    }
                    break;
                case 'field2':
                    $values['qcfname2'] = filter_var( $values['qcfname2'], FILTER_SANITIZE_STRING );
                    if ( empty($values['qcfname2']) || $values['qcfname2'] == $qcf['label'][$name] || !strpos( $values['qcfname2'], '.' ) ) {
                        $errors['qcfname2'] = '<p class="qcf-input-error"><span>' . $error['field2'] . '</span></p>';
                    }
                    break;
                case 'field3':
                    $values['qcfname3'] = filter_var( $values['qcfname3'], FILTER_SANITIZE_STRING );
                    if ( empty($values['qcfname3']) || $values['qcfname3'] == $qcf['label'][$name] ) {
                        $errors['qcfname3'] = '<p class="qcf-input-error"><span>' . $error['field3'] . '</span></p>';
                    }
                    break;
                case 'field4':
                    $values['qcfname4'] = strip_tags( stripslashes( $values['qcfname4'] ), $qcf['htmltags'] );
                    if ( empty($values['qcfname4']) || $values['qcfname4'] == $qcf['label'][$name] ) {
                        $errors['qcfname4'] = '<p class="qcf-input-error"><span>' . $error['field4'] . '</span></p>';
                    }
                    break;
                case 'field5':
                    
                    if ( $qcf['selectora'] == 'checkboxa' ) {
                        $check = '';
                        $arr = explode( ",", $qcf['dropdownlist'] );
                        foreach ( $arr as $item ) {
                            $check = $check . $values['qcfname5_' . str_replace( ' ', '', $item )];
                        }
                        if ( empty($check) ) {
                            $errors['qcfname5'] = '<p class="qcf-input-error"><span>' . $error['field5'] . '</span></p>';
                        }
                    } else {
                        $values['qcfname5'] = filter_var( $values['qcfname5'], FILTER_SANITIZE_STRING );
                        if ( empty($values['qcfname5']) || $values['qcfname5'] == $qcf['label'][$name] && $qcf['selectora'] != 'radioa' ) {
                            $errors['qcfname5'] = '<p class="qcf-input-error"><span>' . $error['field5'] . '</span></p>';
                        }
                    }
                    
                    break;
                case 'field6':
                    
                    if ( $qcf['selectorb'] == 'checkboxb' ) {
                        $check = '';
                        $arr = explode( ",", $qcf['checklist'] );
                        foreach ( $arr as $item ) {
                            $check = $check . $values['qcfname6_' . str_replace( ' ', '', $item )];
                        }
                        if ( empty($check) ) {
                            $errors['qcfname6'] = '<p class="qcf-input-error"><span>' . $error['field6'] . '</span></p>';
                        }
                    } else {
                        $values['qcfname6'] = filter_var( $values['qcfname6'], FILTER_SANITIZE_STRING );
                        if ( empty($values['qcfname6']) || $values['qcfname6'] == $qcf['label'][$name] && $qcf['selectorb'] != 'radiob' ) {
                            $errors['qcfname6'] = '<p class="qcf-input-error"><span>' . $error['field6'] . '</span></p>';
                        }
                    }
                    
                    break;
                case 'field7':
                    
                    if ( $qcf['selectorc'] == 'checkboxc' ) {
                        $check = '';
                        $arr = explode( ",", $qcf['radiolist'] );
                        foreach ( $arr as $item ) {
                            $check = $check . $values['qcfname7_' . str_replace( ' ', '', $item )];
                        }
                        if ( empty($check) ) {
                            $errors['qcfname7'] = '<p class="qcf-input-error"><span>' . $error['field7'] . '</span></p>';
                        }
                    } else {
                        $values['qcfname7'] = filter_var( $values['qcfname7'], FILTER_SANITIZE_STRING );
                        if ( empty($values['qcfname7']) || $values['qcfname7'] == $qcf['label'][$name] && $qcf['selectorc'] != 'radioc' ) {
                            $errors['qcfname7'] = '<p class="qcf-input-error"><span>' . $error['field7'] . '</span></p>';
                        }
                    }
                    
                    break;
                case 'field8':
                    $values['qcfname8'] = filter_var( $values['qcfname8'], FILTER_SANITIZE_STRING );
                    if ( empty($values['qcfname8']) || $values['qcfname8'] == $qcf['label'][$name] ) {
                        $errors['qcfname8'] = '<p class="qcf-input-error"><span>' . $error['field8'] . '</span></p>';
                    }
                    break;
                case 'field9':
                    $values['qcfname9'] = filter_var( $values['qcfname9'], FILTER_SANITIZE_STRING );
                    if ( empty($values['qcfname9']) || $values['qcfname9'] == $qcf['label'][$name] ) {
                        $errors['qcfname9'] = '<p class="qcf-input-error"><span>' . $error['field9'] . '</span></p>';
                    }
                    break;
                case 'field10':
                    $values['qcfname10'] = filter_var( $values['qcfname10'], FILTER_SANITIZE_STRING );
                    if ( empty($values['qcfname10']) || $values['qcfname10'] == $qcf['label'][$name] ) {
                        $errors['qcfname10'] = '<p class="qcf-input-error"><span>' . $error['field10'] . '</span></p>';
                    }
                    break;
                case 'field11':
                    $values['qcfname11'] = filter_var( $values['qcfname11'], FILTER_SANITIZE_STRING );
                    if ( empty($values['qcfname11']) || $values['qcfname11'] == $qcf['label'][$name] ) {
                        $errors['qcfname11'] = '<p class="qcf-input-error"><span>' . $error['field11'] . '</span></p>';
                    }
                    break;
                case 'field12':
                    $values['qcfname12'] = filter_var( $values['qcfname12'], FILTER_SANITIZE_STRING );
                    if ( $values['qcfname12'] != $values['answer'] ) {
                        $errors['qcfname12'] = '<p class="qcf-input-error"><span>' . $error['mathsanswer'] . '</span></p>';
                    }
                    if ( empty($values['qcfname12']) ) {
                        $errors['qcfname12'] = '<p class="qcf-input-error"><span>' . $error['mathsmissing'] . '</span></p>';
                    }
                    break;
                case 'field13':
                    $values['qcfname13'] = filter_var( $values['qcfname13'], FILTER_SANITIZE_STRING );
                    if ( empty($values['qcfname13']) || $values['qcfname13'] == $qcf['label'][$name] ) {
                        $errors['qcfname13'] = '<p class="qcf-input-error"><span>' . $error['field13'] . '</span></p>';
                    }
                    break;
                case 'field15':
                    $values['qcfname15'] = filter_var( $values['qcfname15'], FILTER_SANITIZE_STRING );
                    if ( empty($values['qcfname15']) ) {
                        $errors['qcfname15'] = '<p class="qcf-input-error"><span>' . $error['consent'] . '</span></p>';
                    }
                    break;
            }
        }
    }
    for ( $i = 1 ;  $i <= $attach['qcf_number'] ;  $i++ ) {
        if ( !isset( $_FILES['filename' . $i]['name'] ) || empty($_FILES['filename' . $i]['name']) ) {
            continue;
        }
        $tmp_name = $_FILES['filename' . $i]['tmp_name'];
        $name = $_FILES['filename' . $i]['name'];
        $size = $_FILES['filename' . $i]['size'];
        
        if ( file_exists( $tmp_name ) ) {
            $found = 'checked';
            if ( $size > $attach['qcf_attach_size'] ) {
                $errors['attach'] = '<p class="qcf-input-error"><span>' . $attach['qcf_attach_error_size'] . '</span></p>';
            }
            $ext = strtolower( substr( strrchr( $name, '.' ), 1 ) );
            if ( strpos( $attach['qcf_attach_type'], $ext ) === false ) {
                $errors['attach'] = '<p class="qcf-input-error"><span>' . $attach['qcf_attach_error_type'] . '</span></p>';
            }
        }
    
    }
    if ( isset( $errors['attach'] ) && $errors['attach'] && $attach['qcf_number'] > 1 ) {
        $errors['attach'] = '<p class="qcf-input-error"><span>' . $attach['qcf_attach_error'] . '</span></p>';
    }
    if ( isset( $attach['qcf_required'] ) && $attach['qcf_required'] && !$found ) {
        $errors['attach'] = '<p class="qcf-input-error"><span>' . $attach['qcf_attach_error_required'] . '</span></p>';
    }
    return count( $errors ) == 0;
}

/*
	@Change
	@Added $ajax argument -- defaults to false
*/
function qcf_process_form( $values, $id )
{
    /**
     * @var \Freemius $quick_contact_form_fs Object for freemius.
     */
    global  $quick_contact_form_fs ;
    $qcf_setup = qcf_get_stored_setup();
    $qcf = qcf_get_stored_options( $id );
    $reply = qcf_get_stored_reply( $id );
    $style = qcf_get_stored_style( $id );
    $attach = qcf_get_stored_attach( $id );
    $auto = qcf_get_stored_autoresponder( $id );
    $hd = ( $style['header-type'] ? $style['header-type'] : 'h2' );
    $qcfemail = qcf_get_stored_email();
    $qcf_email = ( $qcfemail[$id] ? $qcfemail[$id] : get_bloginfo( 'admin_email' ) );
    if ( isset( $_GET["email"] ) ) {
        $qcf_email = sanitize_email( $_GET["email"] );
    }
    $values['qcfname2'] = ( $values['qcfname2'] ? $values['qcfname2'] : $qcf_email );
    if ( !empty($reply['replytitle']) ) {
        $reply['replytitle'] = apply_filters( 'qcf_reply_title_h2_markup', '<' . $hd . ' class="reply-title">' ) . $reply['replytitle'] . apply_filters( 'qcf_reply_title_end_h2_markup', '</' . $hd . '>' );
    }
    if ( !empty($reply['replyblurb']) ) {
        $reply['replyblurb'] = apply_filters( 'qcf_reply_blurb_p_markup', '<p class="reply-blurb">' ) . $reply['replyblurb'] . apply_filters( 'qcf_reply_blurb_end_p_markup', '</p>' );
    }
    if ( $reply['subjectoption'] == "sendername" ) {
        $addon = $values['qcfname1'];
    }
    if ( $reply['subjectoption'] == "sendersubj" ) {
        $addon = $values['c'];
    }
    if ( $reply['subjectoption'] == "senderpage" ) {
        //	$addon = $pagetitle;
    }
    if ( $reply['subjectoption'] == "sendernone" ) {
        $addon = '';
    }
    
    if ( $reply['fromemail'] ) {
        $from = 'From: ' . $reply['fromemail'] . "\r\n" . 'Reply-To: "' . $values['qcfname1'] . '" <' . $values['qcfname2'] . '>' . "\r\n";
    } else {
        $from = 'From: "' . $values['qcfname1'] . '" <' . $values['qcfname2'] . '>' . "\r\n";
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $page = get_the_title();
    if ( empty($page) ) {
        $page = 'quick contact form';
    }
    foreach ( explode( ',', $qcf['sort'] ) as $item ) {
        if ( $qcf['active_buttons'][$item] ) {
            switch ( $item ) {
                case 'field1':
                    if ( $values['qcfname1'] == $qcf['label'][$item] ) {
                        $values['qcfname1'] = '';
                    }
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname1'] ) ) . '</p>';
                    $ac_d['name'] = strip_tags( stripslashes( $values['qcfname1'] ) );
                    break;
                case 'field2':
                    if ( $values['qcfname2'] == $qcf['label'][$item] ) {
                        $values['qcfname2'] = '';
                    }
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname2'] ) ) . '</p>';
                    $ac_d['email'] = strip_tags( stripslashes( $values['qcfname2'] ) );
                    break;
                case 'field3':
                    if ( $values['qcfname3'] == $qcf['label'][$item] ) {
                        $values['qcfname3'] = '';
                    }
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname3'] ) ) . '</p>';
                    $ac_d['phone'] = strip_tags( stripslashes( $values['qcfname3'] ) );
                    break;
                case 'field4':
                    if ( $values['qcfname4'] == $qcf['label'][$item] ) {
                        $values['qcfname4'] = '';
                    }
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname4'] ), $qcf['htmltags'] ) . '</p>';
                    break;
                case 'field5':
                    
                    if ( $qcf['selectora'] == 'checkboxa' ) {
                        $checks = '';
                        $arr = explode( ",", $qcf['dropdownlist'] );
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>';
                        foreach ( $arr as $key ) {
                            if ( $values['qcfname5_' . str_replace( ' ', '', $key )] ) {
                                $checks .= $key . ', ';
                            }
                        }
                        $values['qcfname5'] = rtrim( $checks, ', ' );
                        $content .= $values['qcfname5'] . '</p>';
                    } else {
                        if ( $values['qcfname5'] == $qcf['label'][$item] ) {
                            $values['qcfname5'] = '';
                        }
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . $values['qcfname5'] . '</p>';
                    }
                    
                    break;
                case 'field6':
                    
                    if ( $qcf['selectorb'] == 'checkboxb' ) {
                        $checks = '';
                        $arr = explode( ",", $qcf['checklist'] );
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>';
                        foreach ( $arr as $key ) {
                            if ( $values['qcfname6_' . str_replace( ' ', '', $key )] ) {
                                $checks .= $key . ', ';
                            }
                        }
                        $values['qcfname6'] = rtrim( $checks, ', ' );
                        $content .= $values['qcfname6'] . '</p>';
                    } else {
                        if ( $values['qcfname6'] == $qcf['label'][$item] ) {
                            $values['qcfname6'] = '';
                        }
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . $values['qcfname6'] . '</p>';
                    }
                    
                    break;
                case 'field7':
                    
                    if ( $qcf['selectorc'] == 'checkboxc' ) {
                        $checks = '';
                        $arr = explode( ",", $qcf['radiolist'] );
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>';
                        foreach ( $arr as $key ) {
                            if ( $values['qcfname7_' . str_replace( ' ', '', $key )] ) {
                                $checks .= $key . ', ';
                            }
                        }
                        $values['qcfname7'] = rtrim( $checks, ', ' );
                        $content .= $values['qcfname7'] . '</p>';
                    } else {
                        if ( $values['qcfname7'] == $qcf['label'][$item] ) {
                            $values['qcfname7'] = '';
                        }
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . $values['qcfname7'] . '</p>';
                    }
                    
                    break;
                case 'field8':
                    if ( $values['qcfname8'] == $qcf['label'][$item] ) {
                        $values['qcfname8'] = '';
                    }
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname8'] ) ) . '</p>';
                    break;
                case 'field9':
                    if ( $values['qcfname9'] == $qcf['label'][$item] ) {
                        $values['qcfname9'] = '';
                    }
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname9'] ) ) . '</p>';
                    break;
                case 'field10':
                    if ( $values['qcfname10'] == $qcf['label'][$item] ) {
                        $values['qcfname10'] = '';
                    }
                    if ( !empty($values['qcfname10']) ) {
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( $values['qcfname10'] ) . '</p>';
                    }
                    break;
                case 'field11':
                    if ( $values['qcfname11'] == $qcf['label'][$item] ) {
                        $values['qcfname11'] = '';
                    }
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname11'] ) ) . '</p>';
                    break;
                case 'field13':
                    if ( $values['qcfname13'] == $qcf['label'][$item] ) {
                        $values['qcfname13'] = '';
                    }
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname13'] ) ) . '</p>';
                    break;
                case 'field14':
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname14'] ) ) . '</p>';
                    break;
                case 'field15':
                    $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags( stripslashes( $values['qcfname15'] ) ) . '</p>';
                    break;
            }
        }
    }
    $sendcontent = "<html>" . apply_filters( 'qcf_body_head_h2_markup', '<h2 class="body-head">' ) . $reply['bodyhead'] . apply_filters( 'qcf_body_head_end_h2_markup', '</h2>' ) . $content;
    $copycontent = "<html>";
    if ( $reply['replymessage'] ) {
        $copycontent .= $reply['replymessage'];
    }
    if ( $reply['replycopy'] ) {
        $copycontent .= $content;
    }
    if ( $reply['page'] ) {
        $sendcontent .= "<p>Message was sent from: <b>" . $page . "</b></p>";
    }
    if ( $reply['tracker'] ) {
        $sendcontent .= "<p>Senders IP address: <b>" . $ip . "</b></p>";
    }
    if ( $reply['url'] ) {
        $sendcontent .= "<p>URL: <b>" . $url . "</b></p>";
    }
    $subject = "{$reply['subject']} {$addon}";
    $attachments = array();
    if ( !function_exists( 'wp_handle_upload' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    add_filter( 'upload_dir', 'qcf_upload_dir' );
    $dir = ( realpath( WP_CONTENT_DIR . '/uploads/qcf/' ) ? '/uploads/qcf/' : '/uploads/' );
    $url = get_site_url();
    $att = $b = array();
    $upload_dir = wp_upload_dir();
    $upload = $upload_dir['basedir'];
    for ( $i = 1 ;  $i <= $attach['qcf_number'] ;  $i++ ) {
        $filename = $_FILES['filename' . $i]['tmp_name'];
        
        if ( file_exists( $filename ) ) {
            $name = $_FILES['filename' . $i]['name'];
            $name = trim( preg_replace( '/[^A-Za-z0-9. ]/', '', $name ) );
            $name = str_replace( ' ', '-', $name );
            if ( file_exists( $upload . '/qcf/' . $name ) ) {
                $name = 'x' . $name;
            }
            $_FILES['filename' . $i]['name'] = $name;
            $uploadedfile = $_FILES['filename' . $i];
            $upload_overrides = array(
                'test_form' => false,
            );
            $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
            
            if ( !$attach['qcf_attach_link'] ) {
                array_push( $attachments, WP_CONTENT_DIR . $dir . $name );
                $b['url'] = $url . '/wp-content' . $dir . $name;
                $b['file'] = $name;
                array_push( $att, $b );
            }
            
            $gotlinks = true;
        }
    
    }
    remove_filter( 'upload_dir', 'qcf_upload_dir' );
    
    if ( $attach['qcf_attach_link'] && $gotlinks ) {
        $sendcontent .= apply_filters( 'qcf_attach_link_h2_markup', '<h2 class="attach-link">' ) . esc_html__( 'Attachments:', 'quick-contact-form' ) . apply_filters( 'qcf_attach_link_h2_markup', '</h2>' );
        for ( $i = 1 ;  $i <= $attach['qcf_number'] ;  $i++ ) {
            $filename = $_FILES['filename' . $i]['name'];
            $filename = trim( preg_replace( '/[^A-Za-z0-9. ]/', '', $filename ) );
            $filename = str_replace( ' ', '-', $filename );
            if ( $filename ) {
                $sendcontent .= '<p><a href = "' . $url . '/wp-content' . $dir . $filename . '">' . $filename . '</a><br>';
            }
        }
        $sendcontent .= '</p>';
    }
    
    $sendcontent .= "</html>";
    $copycontent .= "</html>";
    $headers = $from;
    
    if ( $reply['qcf_bcc'] ) {
        $headers .= "BCC: " . $qcf_email . "\r\n";
        $qcf_email = 'null';
    }
    
    $headers .= "MIME-Version: 1.0\r\n" . "Content-Type: text/html; charset=\"utf-8\"\r\n";
    $message = $sendcontent;
    $emails = qcf_get_stored_emails( $id );
    
    if ( function_exists( 'qcf_select_email' ) || $emails['emailenable'] ) {
        $email = qcf_redirect_by_email( $id, $values['qcfname5'] );
        if ( $email ) {
            $qcf_email = $email;
        }
    }
    
    qcf_wp_mail(
        'Admin',
        $qcf_email,
        $subject,
        $message,
        $headers,
        $attachments
    );
    
    if ( !$qcf_setup['nostore'] || $values['qcfname15'] ) {
        $qcf_messages = get_option( 'qcf_messages' . $id, array() );
        if ( $values['qcfname1'] == $qcf['label']['field1'] ) {
            $values['qcfname1'] = '';
        }
        $sentdate = date_i18n( 'd M Y' );
        $qcf_messages[] = array(
            'field0'      => $sentdate,
            'field1'      => $values['qcfname1'],
            'field2'      => $values['qcfname2'],
            'field3'      => $values['qcfname3'],
            'field4'      => $values['qcfname4'],
            'field5'      => $values['qcfname5'],
            'field6'      => $values['qcfname6'],
            'field7'      => $values['qcfname7'],
            'field8'      => $values['qcfname8'],
            'field9'      => $values['qcfname9'],
            'field10'     => $values['qcfname10'],
            'field11'     => $values['qcfname11'],
            'field13'     => $values['qcfname13'],
            'field14'     => $values['qcfname14'],
            'field15'     => $values['qcfname15'],
            'attachments' => $att,
        );
        update_option( 'qcf_messages' . $id, $qcf_messages );
    }
    
    if ( $auto['enable'] && $values['qcfname2'] ) {
        qcf_send_confirmation(
            $values,
            $content,
            $id,
            $qcf_email
        );
    }
    do_action( 'qcf_post_email', $values, $id );
    
    if ( isset( $reply['createuser'] ) && $reply['createuser'] ) {
        qcf_create_user( $values );
        do_action( 'qcf_post_user_creation', $values, $id );
    }
    
    $url = false;
    
    if ( $reply['qcf_reload'] ) {
        $_POST = array();
        $reloadinterval = ( $reply['qcf_reload_time'] ? $reply['qcf_reload_time'] : 0 );
    }
    
    
    if ( $reply['qcf_redirect'] ) {
        $wheretogo = qcf_get_stored_redirect( $id );
        if ( function_exists( 'qcf_select_redirect' ) || $wheretogo['redirectenable'] ) {
            $redirect = qcf_redirect_by_selection( $id, $values );
        }
        
        if ( $redirect ) {
            $location = $redirect;
        } else {
            $location = $reply['qcf_redirect_url'];
        }
        
        $url = ';url=' . $location;
    }
    
    wp_add_inline_script( 'qcf_script', 'document.getElementById("qcf_reload").scrollIntoView();' );
    
    if ( $id ) {
        $formstyle = $id;
    } else {
        $formstyle = 'default';
    }
    
    $replycontent = "<a id='qcf_reload'></a><br><div class='qcf-style " . $formstyle . "'>\r\t\n    <div id='" . $style['border'] . "'>\r\t";
    $replycontent .= $reply['replytitle'] . $reply['replyblurb'];
    if ( $reply['messages'] ) {
        $replycontent .= $content;
    }
    $replycontent .= '</div></div>';
    $redirecting = "<a id='qcf_reload'></a><br><div class='qcf-style " . $formstyle . "'>\r\t\n    <div id='" . $style['border'] . "'>\r\t";
    $redirecting .= apply_filters( 'qcf_redirecting_h2_markup', '<' . $hd . ' class="redirecting">' ) . esc_html__( 'Redirecting...', 'quick-contact-form' ) . apply_filters( 'qcf_redirecting_h2_markup', '</' . $hd . '>' );
    $redirecting .= '</div></div>';
    
    if ( $reply['qcf_redirect'] && $reply['qcf_reload'] ) {
        echo  '<meta http-equiv="refresh" content="' . esc_attr( $reloadinterval ) . esc_url( $url ) . '">' . wp_kses_post( $replycontent ) ;
    } elseif ( $reply['qcf_redirect'] && !$reply['qcf_reload'] ) {
        echo  '<meta http-equiv="refresh" content="0' . esc_attr( $url ) . '">' . wp_kses_post( $redirecting ) ;
    } elseif ( !$reply['qcf_redirect'] && $reply['qcf_reload'] ) {
        echo  '<meta http-equiv="refresh" content="' . esc_attr( $reloadinterval ) . '">' . wp_kses_post( $replycontent ) ;
    } else {
        echo  wp_kses_post( $replycontent ) ;
    }

}

function qcf_send_confirmation(
    $values,
    $content,
    $id,
    $qcf_email
)
{
    $auto = qcf_get_stored_autoresponder( $id );
    if ( empty($auto['fromemail']) ) {
        $auto['fromemail'] = $qcf_email;
    }
    if ( empty($auto['fromname']) ) {
        $auto['fromname'] = get_bloginfo( 'name' );
    }
    $headers = 'From: "' . $auto['fromname'] . '" <' . $auto['fromemail'] . '>' . "\r\n" . "MIME-Version: 1.0\r\n" . "Content-Type: text/html; charset=\"utf-8\"\r\n";
    $subject = $auto['subject'];
    $message = '<html>' . $auto['message'];
    $message = str_replace( '[name]', $values['qcfname1'], $message );
    $message = str_replace( '[date]', $values['qcfname10'], $message );
    $message = str_replace( '[option]', $values['qcfname5'], $message );
    if ( $auto['sendcopy'] ) {
        $message .= $content;
    }
    $message .= '<html>';
    qcf_wp_mail(
        'Confirmation',
        $values['qcfname2'],
        $subject,
        $message,
        $headers
    );
}

function qcf_create_user( $values )
{
    $user_name = $values['qcfname1'];
    $user_email = $values['qcfname2'];
    $user_id = username_exists( $user_name );
    
    if ( !$user_id and email_exists( $user_email ) == false and $user_name and $user_email ) {
        $password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
        $user_id = wp_create_user( $user_name, $password, $user_email );
        wp_update_user( array(
            'ID'   => $user_id,
            'role' => 'subscriber',
        ) );
        wp_new_user_notification( $user_id, $notify = 'both' );
    }

}

function qcf_validate_form_callback()
{
    $formvalues = qcf_santitize_formvalues( $_POST );
    $formerrors = array();
    $json = (object) array(
        'success' => false,
        'errors'  => array(),
        'display' => '',
    );
    
    if ( isset( $formvalues['id'] ) ) {
        $id = $formvalues['id'];
    } else {
        echo  wp_json_encode( $json ) ;
    }
    
    
    if ( !qcf_verify_form( $formvalues, $formerrors, $id ) ) {
        $error = qcf_get_stored_error( $id );
        $json->display = $error['errortitle'];
        $json->blurb = $error['errorblurb'];
        /* Format Form Errors */
        foreach ( $formerrors as $k => $v ) {
            $json->errors[] = (object) array(
                'name'  => $k,
                'error' => $v,
            );
        }
    } else {
        $json->success = true;
        ob_start();
        qcf_process_form( $formvalues, $id );
        $json->display = ob_get_clean();
    }
    
    echo  wp_json_encode( $json ) ;
    wp_die();
}

function qcf_santitize_formvalues( $form )
{
    $form_data = array(
        'id'        => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname1'  => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname2'  => array(
        'sanitize' => 'sanitize_email',
        'default'  => '',
    ),
        'qcfname3'  => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname4'  => array(
        'sanitize' => 'sanitize_textarea_field',
        'default'  => '',
    ),
        'qcfname5'  => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname6'  => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname7'  => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname8'  => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname9'  => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname10' => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname11' => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname12' => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname13' => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname14' => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'qcfname15' => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'action'    => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'thesum'    => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'answer'    => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
        'form_id'   => array(
        'sanitize' => 'sanitize_text_field',
        'default'  => '',
    ),
    );
    $sanitized_form = array();
    foreach ( $form_data as $key => $value ) {
        
        if ( isset( $form[$key] ) ) {
            $sanitized_form[$key] = call_user_func( $value['sanitize'], $form[$key] );
        } else {
            $sanitized_form[$key] = $value['default'];
        }
    
    }
    return $sanitized_form;
}

function qcf_loop( $id )
{
    ob_start();
    $digit1 = mt_rand( 1, 10 );
    $digit2 = mt_rand( 1, 10 );
    
    if ( $digit2 >= $digit1 ) {
        $values['thesum'] = "{$digit1} + {$digit2}";
        $values['answer'] = $digit1 + $digit2;
    } else {
        $values['thesum'] = "{$digit1} - {$digit2}";
        $values['answer'] = $digit1 - $digit2;
    }
    
    $qcf = qcf_get_stored_options( $id );
    for ( $i = 1 ;  $i <= 14 ;  $i++ ) {
        if ( isset( $qcf['label']['field' . $i] ) ) {
            $values['qcfname' . $i] = $qcf['label']['field' . $i];
        }
    }
    
    if ( is_user_logged_in() && isset( $qcf['showuser'] ) && $qcf['showuser'] ) {
        $current_user = wp_get_current_user();
        $values['qcfname1'] = $current_user->user_login;
        $values['qcfname2'] = $current_user->user_email;
    }
    
    $values['qcfname12'] = '';
    qcf_display_form( $values, array(), $id );
    $output_string = ob_get_contents();
    ob_end_clean();
    return $output_string;
}

class qcf_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'qcf_widget',
            // Base ID
            __( 'Quick Contact Form', 'quick-contact-form' ),
            // Name
            array(
                'description' => __( 'Add the Quick Contact Form to your sidebar', 'quick-contact-form' ),
            )
        );
    }
    
    function form( $instance )
    {
        $instance = wp_parse_args( (array) $instance, array(
            'formname' => '',
        ) );
        $formname = $instance['formname'];
        $qcf_setup = qcf_get_stored_setup();
        echo  'Select Form:</ br>' ;
        ?>
        <select class="widefat" name="<?php 
        echo  esc_attr( $this->get_field_name( 'formname' ) ) ;
        ?>">
			<?php 
        $arr = explode( ",", $qcf_setup['alternative'] );
        foreach ( $arr as $item ) {
            
            if ( $item == '' ) {
                $showname = 'default';
                $item = '';
            } else {
                $showname = $item;
            }
            
            
            if ( $showname == $formname || $formname == '' ) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            
            ?>
                <option value="<?php 
            echo  esc_attr( $item ) ;
            ?>"
                        id="<?php 
            echo  esc_attr( $this->get_field_id( 'formname' ) ) ;
            ?>" <?php 
            echo  esc_attr( $selected ) ;
            ?>><?php 
            echo  esc_html( $showname ) ;
            ?>
                </option>
				<?php 
        }
        ?>
        </select>
        <p><?php 
        _e( sprintf( 'All options for the quick contact form are changed on the plugin %1$sSettings%2$s page.', '<a href="options-general.php?page=quick-contact-form">', '</a>' ), 'quick-contact-form' );
        ?></p>
		<?php 
    }
    
    function update( $new_instance, $old_instance )
    {
        $instance = $old_instance;
        $instance['formname'] = $new_instance['formname'];
        return $instance;
    }
    
    function widget( $args, $instance )
    {
        extract( $args, EXTR_SKIP );
        $id = preg_replace( "/[^A-Za-z]/", '', $instance['formname'] );
        echo  qcf_kses_forms( qcf_loop( $id ) ) ;
    }

}
function qcf_generate_css()
{
    $qcf_form = qcf_get_stored_setup();
    $arr = explode( ",", $qcf_form['alternative'] );
    $code = '';
    foreach ( $arr as $item ) {
        $handle = $header = $font = $inputfont = $submitfont = $fontoutput = $border = '';
        $headercolour = $headersize = $corners = $input = $background = $submitwidth = $paragraph = $submitbutton = $submit = '';
        $style = qcf_get_stored_style( $item );
        $hd = ( $style['header-type'] ? $style['header-type'] : 'h2' );
        
        if ( $item != '' ) {
            $id = '.' . $item;
        } else {
            $id = '.default';
        }
        
        
        if ( $style['font'] == 'plugin' ) {
            $font = "font-family: " . $style['text-font-family'] . "; font-size: " . $style['text-font-size'] . ";color: " . $style['text-font-colour'] . ";height:auto;";
            $inputfont = "font-family: " . $style['font-family'] . "; font-size: " . $style['font-size'] . "; color: " . $style['font-colour'] . ";";
            $submitfont = "font-family: " . $style['font-family'];
            if ( $style['header-size'] ) {
                $headersize = "font-size: " . $style['header-size'] . ";";
            }
            if ( $style['header-colour'] ) {
                $headercolour = "color: " . $style['header-colour'] . ";";
            }
            $header = ".qcf-style" . $id . " " . $hd . " {" . $headercolour . $headersize . ";height:auto;}";
        }
        
        $input = ".qcf-style" . $id . " input[type=text], .qcf-style" . $id . " input[type=email], .qcf-style" . $id . " textarea, .qcf-style" . $id . " select {border: " . $style['input-border'] . ";background:" . $style['inputbackground'] . ";" . $inputfont . ";line-height:normal;height:auto; " . $style['line_margin'] . "}\r\n";
        $input .= ".qcf-style" . $id . " .qcfcontainer input + label, .qcf-style" . $id . " .qcfcontainer textarea + label {" . $inputfont . ";}\r\n";
        $focus = ".qcf-style" . $id . " input:focus, .qcf-style" . $id . " textarea:focus {background:" . $style['inputfocus'] . ";}\r\n";
        $paragraph = ".qcf-style" . $id . " p, .qcf-style" . $id . " select{" . $font . "line-height:normal;height:auto;}\r\n";
        $required = ".qcf-style" . $id . " input[type=text].required, .qcf-style" . $id . " input[type=email].required, .qcf-style" . $id . " select.required, .qcf-style" . $id . " textarea.required {border: " . $style['input-required'] . ";}\r\n";
        $error = ".qcf-style" . $id . " p span, .qcf-style" . $id . " .error {color:" . $style['error-font-colour'] . ";clear:both;}\r\n\n.qcf-style" . $id . " input[type=text].error, .qcf-style" . $id . " input[type=email].error,.qcf-style" . $id . " select.error, .qcf-style" . $id . " textarea.error {border:" . $style['error-border'] . ";}\r\n";
        if ( $style['submitwidth'] == 'submitpercent' ) {
            $submitwidth = 'width:100%;';
        }
        if ( $style['submitwidth'] == 'submitrandom' ) {
            $submitwidth = 'width:auto;';
        }
        if ( $style['submitwidth'] == 'submitpixel' ) {
            $submitwidth = 'width:' . $style['submitwidthset'] . ';';
        }
        
        if ( $style['submitposition'] == 'submitleft' ) {
            $submitposition = 'float:left;';
        } else {
            $submitposition = 'float:right;';
        }
        
        
        if ( !$style['submit-button'] ) {
            $submit = "color:" . $style['submit-colour'] . ";background:" . $style['submit-background'] . ";border:" . $style['submit-border'] . ";" . $submitfont . ";font-size: inherit;";
            $submithover = "background:" . $style['submit-hover-background'] . ";";
        } else {
            $submit = 'border:none;padding:none;height:auto;overflow:hidden;';
        }
        
        $submitbutton = ".qcf-style" . $id . " #submit {" . $submitposition . $submitwidth . $submit . "}\r\n";
        $submitbutton .= ".qcf-style" . $id . " #submit:hover{" . $submithover . "}\r\n";
        if ( $style['border'] != 'none' ) {
            $border = ".qcf-style" . $id . " #" . $style['border'] . " {border:" . $style['form-border'] . ";}\r\n";
        }
        if ( $style['background'] == 'white' ) {
            $background = ".qcf-style" . $id . " div {background:#FFF;}\r\n";
        }
        if ( $style['background'] == 'color' ) {
            $background = ".qcf-style" . $id . " div {background:" . $style['backgroundhex'] . ";}\r\n";
        }
        if ( $style['backgroundimage'] ) {
            $background = ".qcf-style" . $id . " div {background: url('" . $style['backgroundimage'] . "');}\r\n";
        }
        $formwidth = preg_split( '#(?<=\\d)(?=[a-z%])#i', $style['width'] );
        if ( !isset( $formwidth[1] ) || empty($formwidth[1]) ) {
            $formwidth[1] = 'px';
        }
        
        if ( $style['widthtype'] == 'pixel' ) {
            $width = $formwidth[0] . $formwidth[1];
        } else {
            $width = '100%';
        }
        
        
        if ( $style['corners'] == 'round' ) {
            $corner = '5px';
        } else {
            $corner = '0';
        }
        
        $corners = ".qcf-style" . $id . " input[type=text], .qcf-style" . $id . " input[type=email],.qcf-style" . $id . " textarea, .qcf-style" . $id . " select, .qcf-style" . $id . " #submit {border-radius:" . $corner . ";}\r\n";
        if ( $style['corners'] == 'theme' ) {
            $corners = '';
        }
        if ( !isset( $style['slider-thickness'] ) ) {
            $style['slider-thickness'] = 1;
        }
        $handle = (int) $style['slider-thickness'] + 1;
        $slider = '.qcf-style' . $id . ' div.rangeslider, .qcf-style' . $id . ' div.rangeslider__fill {height: ' . $style['slider-thickness'] . 'em;background: ' . $style['slider-background'] . ';}
.qcf-style' . $id . ' div.rangeslider__fill {background: ' . $style['slider-revealed'] . ';}
.qcf-style' . $id . ' div.rangeslider__handle {background: ' . $style['handle-background'] . ';border: 1px solid ' . $style['handle-border'] . ';width: ' . $handle . 'em;height: ' . $handle . 'em;position: absolute;top: -0.5em;-webkit-border-radius:' . $style['handle-colours'] . '%;-moz-border-radius:' . $style['handle-corners'] . '%;-ms-border-radius:' . $style['handle-corners'] . '%;-o-border-radius:' . $style['handle-corners'] . '%;border-radius:' . $style['handle-corners'] . '%;}
.qcf-style' . $id . ' div.qcf-slideroutput{font-size:' . $style['output-size'] . ';color:' . $style['output-colour'] . ';}';
        $code .= ".qcf-style" . $id . " {max-width:100%;overflow:hidden;width:" . $width . ";}\r\n" . $border . $corners . $header . $paragraph . $slider . $input . $focus . $required . $error . $background . $submitbutton;
    }
    return $code;
}

function qcf_head_css()
{
    // nothing
}

function qcf_style_scripts()
{
    $qcf_form = qcf_get_stored_setup();
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( "jquery-effects-core" );
    wp_enqueue_script(
        'qcf_script',
        plugins_url( 'scripts.js', __FILE__ ),
        array( 'jquery', 'jquery-ui-datepicker', 'jquery-effects-core' ),
        null,
        true
    );
    wp_add_inline_script( 'qcf_script', 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";' );
    wp_enqueue_script(
        'qcf_slider',
        plugins_url( 'slider.js', __FILE__ ),
        array( 'jquery' ),
        null,
        true
    );
    
    if ( !$qcf_form['nostyling'] ) {
        wp_enqueue_style( 'qcf_style', plugins_url( 'styles.css', __FILE__ ) );
        wp_add_inline_style( 'qcf_style', qcf_generate_css() );
    }
    
    
    if ( !$qcf_form['noui'] ) {
        $b = wp_enqueue_style( 'jquery-style', QUICK_CONTACT_FORM_PLUGIN_URL . 'ui/user/css/jquery-ui-smoothness-1-11-2.css' );
        $c = 1;
    }

}

function qcf_start( $atts )
{
    $atts = shortcode_atts( array(
        'id'   => '',
        'form' => '',
    ), $atts );
    
    if ( !empty($atts['id']) ) {
        $id = $atts['id'];
    } else {
        $id = $atts['form'];
    }
    
    $id = preg_replace( "/[^A-Za-z]/", '', $id );
    return qcf_loop( $id );
}

function qcf_plugin_action_links( $links, $file )
{
    
    if ( false !== strpos( $file, '/quick-contact-form.php' ) ) {
        $qcf_links = '<a href="' . get_admin_url() . 'options-general.php?page=quick-contact-form">' . __( 'Settings' ) . '</a>';
        array_unshift( $links, $qcf_links );
    }
    
    return $links;
}

function qcf_upload_dir( $dir )
{
    return array(
        'path'   => $dir['basedir'] . '/qcf',
        'url'    => $dir['baseurl'] . '/qcf',
        'subdir' => '/qcf',
    ) + $dir;
}

function qcf_current_page_url()
{
    $pageURL = 'http';
    if ( !isset( $_SERVER['HTTPS'] ) ) {
        $_SERVER['HTTPS'] = '';
    }
    if ( !empty($_SERVER["HTTPS"]) ) {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    
    if ( $_SERVER["SERVER_PORT"] != "80" && $_SERVER['SERVER_PORT'] != '443' ) {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    
    return $pageURL;
}

// Redirection from selection
function qcf_redirect_by_selection( $id, $values )
{
    $qcf = qcf_get_stored_options( $id );
    $qcf_redirect = qcf_get_stored_redirect( $id );
    if ( $qcf_redirect['whichlist'] == 'dropdownlist' ) {
        $choice = $values['qcfname5'];
    }
    if ( $qcf_redirect['whichlist'] == 'radiolist' ) {
        $choice = $values['qcfname7'];
    }
    $arr = explode( ",", $qcf[$qcf_redirect['whichlist']] );
    foreach ( $arr as $item ) {
        
        if ( $choice == $item ) {
            $choice = str_replace( ' ', '_', $choice );
            return $qcf_redirect[$choice];
        }
    
    }
}

// Send to email from selection
function qcf_redirect_by_email( $id, $option )
{
    $qcf = qcf_get_stored_options( $id );
    $emails = qcf_get_stored_emails( $id );
    $arr = explode( ",", $qcf['dropdownlist'] );
    foreach ( $arr as $item ) {
        
        if ( $option == $item ) {
            $option = str_replace( ' ', '_', $option );
            return $emails[$option];
        }
    
    }
}

function qcf_wp_mail(
    $type,
    $qcp_email,
    $title,
    $content,
    $headers,
    $attachments = null
)
{
    add_action(
        'wp_mail_failed',
        function ( $wp_error ) {
        /**  @var $wp_error \WP_Error */
        if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG && is_wp_error( $wp_error ) ) {
            trigger_error( 'QCF Email - wp_mail error msg : ' . $wp_error->get_error_message(), E_USER_WARNING );
        }
    },
        10,
        1
    );
    if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG ) {
        trigger_error( 'QCF Email message about to send: ' . $type . ' To: ' . $qcp_email, E_USER_NOTICE );
    }
    $res = wp_mail(
        $qcp_email,
        $title,
        $content,
        $headers,
        $attachments
    );
    if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG ) {
        
        if ( true === $res ) {
            trigger_error( 'QCF Email - wp_mail responded OK : ' . $type . ' To: ' . $qcp_email, E_USER_NOTICE );
        } else {
            trigger_error( 'QCF Email - wp_mail responded FAILED to send : ' . $type . ' To: ' . $qcp_email, E_USER_WARNING );
        }
    
    }
}

function qcf_kses_forms( $html )
{
    $kses_defaults = wp_kses_allowed_html( 'post' );
    $svg_args = array(
        'form'     => array(
        'class'   => true,
        'method'  => true,
        'action'  => true,
        'enctype' => true,
        'id'      => true,
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
        'id'               => true,
        'class'            => true,
        'name'             => true,
        'type'             => true,
        'value'            => true,
        'style'            => true,
        'data-default'     => true,
        'data-rangeslider' => true,
        'min'              => true,
        'max'              => true,
        'step'             => true,
        'placeholder'      => true,
        'size'             => true,
        'src'              => true,
    ),
        'textarea' => array(
        'id'           => true,
        'class'        => true,
        'name'         => true,
        'type'         => true,
        'value'        => true,
        'style'        => true,
        'data-default' => true,
        'placeholder'  => true,
    ),
        'output'   => array(
        'id'    => true,
        'class' => true,
        'style' => true,
    ),
    );
    $allowed_tags = array_merge( $kses_defaults, $svg_args );
    return wp_kses( $html, $allowed_tags );
}
