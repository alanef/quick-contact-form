<?php

function qcf_get_stored_options( $id )
{
    $qcf = get_option( 'qcf_settings' . $id );
    if ( !is_array( $qcf ) ) {
        $qcf = array();
    }
    $default = array(
        'active_buttons' => array(
        'field1'  => 'on',
        'field2'  => 'on',
        'field3'  => '',
        'field4'  => 'on',
        'field5'  => '',
        'field6'  => '',
        'field7'  => '',
        'field8'  => '',
        'field9'  => '',
        'field10' => '',
        'field11' => '',
        'field12' => '',
        'field13' => '',
        'field14' => '',
        'field15' => '',
    ),
        'required'       => array(
        'field1'  => 'checked',
        'field2'  => 'checked',
        'field3'  => '',
        'field4'  => '',
        'field5'  => '',
        'field6'  => '',
        'field7'  => '',
        'field8'  => '',
        'field9'  => '',
        'field10' => '',
        'field11' => '',
        'field12' => 'checked',
        'field13' => '',
        'field14' => '',
        'field15' => '',
    ),
        'label'          => array(
        'field1'  => esc_html__( 'Your Name', 'quick-contact-form' ),
        'field2'  => esc_html__( 'Email', 'quick-contact-form' ),
        'field3'  => esc_html__( 'Telephone', 'quick-contact-form' ),
        'field4'  => esc_html__( 'Message', 'quick-contact-form' ),
        'field5'  => esc_html__( 'Select a value', 'quick-contact-form' ),
        'field6'  => esc_html__( 'Select a value', 'quick-contact-form' ),
        'field7'  => esc_html__( 'Select a value', 'quick-contact-form' ),
        'field8'  => esc_html__( 'Website', 'quick-contact-form' ),
        'field9'  => esc_html__( 'Subject', 'quick-contact-form' ),
        'field10' => esc_html__( 'Select date', 'quick-contact-form' ),
        'field11' => esc_html__( 'Add text', 'quick-contact-form' ),
        'field12' => esc_html__( 'Spambot blocker question', 'quick-contact-form' ),
        'field13' => esc_html__( 'Add text', 'quick-contact-form' ),
        'field14' => esc_html__( 'Select Value', 'quick-contact-form' ),
        'field15' => esc_html__( 'I consent to my data being retained by the site owner after the application has been processed.', 'quick-contact-form' ),
    ),
        'sort'           => 'field1,field2,field3,field4,field5,field6,field7,field10,field8,field9,field11,field13,field14,field12,field15',
        'lines'          => 6,
        'htmltags'       => '<a><b><i>',
        'datepicker'     => 'checked',
        'dropdownlist'   => 'Pound,Dollar,Euro,Yen,Triganic Pu',
        'checklist'      => esc_html__( 'Donald Duck,Mickey Mouse,Goofy', 'quick-contact-form' ),
        'radiolist'      => esc_html__( 'Large,Medium,Small', 'quick-contact-form' ),
        'title'          => esc_html__( 'Enquiry Form', 'quick-contact-form' ),
        'blurb'          => esc_html__( 'Fill in the form below and we will be in touch soon', 'quick-contact-form' ),
        'send'           => esc_html__( 'Send it!', 'quick-contact-form' ),
        'fieldtype'      => 'ttext',
        'fieldtypeb'     => 'btext',
        'selectora'      => 'dropdowna',
        'selectorb'      => 'checkboxb',
        'selectorc'      => 'radioc',
        'min'            => '0',
        'max'            => '100',
        'initial'        => '50',
        'step'           => '10',
        'showuser'       => '',
        'output-values'  => 'checked',
    );
    $default = apply_filters( 'qcf_default_form', $default );
    $qcf = array_merge( $default, $qcf );
    
    if ( strpos( $qcf['sort'], 'field15' ) === false ) {
        $qcf['sort'] = $qcf['sort'] . ',field15';
        $qcf['active_buttons'][] = 'field15';
        $qcf['active_buttons']['field15'] = '';
        $qcf['required'][] = 'field15';
        $qcf['required']['field15'] = '';
        $qcf['label'][] = 'field15';
        $qcf['label']['field15'] = esc_html__( 'I consent to my data being retained by the site owner after the form has been processed.', 'quick-contact-form' );
    }
    
    return $qcf;
}

function qcf_get_stored_attach( $id )
{
    $attach = get_option( 'qcf_attach' . $id );
    if ( !is_array( $attach ) ) {
        $attach = array();
    }
    $default = array(
        'qcf_attach'                => '',
        'qcf_number'                => '3',
        'qcf_attach_label'          => esc_html__( 'Attach an image (Max 100kB)', 'quick-contact-form' ),
        'qcf_attach_size'           => '100000',
        'qcf_attach_type'           => 'jpg,gif,png,pdf',
        'qcf_attach_width'          => '15',
        'qcf_attach_link'           => '',
        'qcf_required'              => '',
        'qcf_attach_error'          => esc_html__( 'There is a problem with your attachment. Please check file formats and size.', 'quick-contact-form' ),
        'qcf_attach_error_size'     => esc_html__( 'File is too big', 'quick-contact-form' ),
        'qcf_attach_error_type'     => esc_html__( 'Filetype not permitted', 'quick-contact-form' ),
        'qcf_attach_error_required' => esc_html__( 'Attachment missing', 'quick-contact-form' ),
    );
    $attach = array_merge( $default, $attach );
    return $attach;
}

function qcf_get_stored_style( $id )
{
    $style = get_option( 'qcf_style' . $id );
    if ( !is_array( $style ) ) {
        $style = array();
    }
    $default = array(
        'font'                    => 'plugin',
        'font-family'             => 'arial, sans-serif',
        'font-size'               => '1em',
        'font-colour'             => '#465069',
        'header'                  => '',
        'header-type'             => 'h2',
        'header-size'             => '1.6em',
        'header-colour'           => '#465069',
        'text-font-family'        => 'arial, sans-serif',
        'text-font-size'          => '1em',
        'text-font-colour'        => '#465069',
        'error-font-colour'       => '#D31900',
        'error-border'            => '1px solid #D31900',
        'width'                   => 280,
        'widthtype'               => 'percent',
        'submitwidth'             => 'submitpercent',
        'submitposition'          => 'submitleft',
        'submitwidthset'          => '',
        'border'                  => 'none',
        'form-border'             => '1px solid #415063',
        'input-border'            => '1px solid #415063',
        'input-required'          => '1px solid #00C618',
        'bordercolour'            => '#415063',
        'inputborderdefault'      => '1px solid #415063',
        'inputborderrequired'     => '1px solid #00C618',
        'inputbackground'         => '#FFFFFF',
        'inputfocus'              => '#FFFFCC',
        'background'              => 'theme',
        'backgroundimage'         => '',
        'backgroundhex'           => '#FFF',
        'submit-colour'           => '#FFF',
        'submit-background'       => '#343838',
        'submit-hover-background' => '#888888',
        'submit-button'           => '',
        'submit-border'           => '1px solid #415063',
        'corners'                 => 'corner',
        'slider-thickness'        => 1,
        'slider-background'       => '#CCC',
        'slider-revealed'         => '#00ff00',
        'handle-background'       => 'white',
        'handle-border'           => '#CCC',
        'handle-corners'          => 50,
        'handle-colours'          => '#FFF',
        'output-size'             => '1em',
        'output-colour'           => '#465069',
        'use_custom'              => '',
        'line_margin'             => 'margin: 2px 0 3px 0;padding: 6px;',
        'styles'                  => ".qcf-style {\r\n\r\n}",
    );
    $style = array_merge( $default, $style );
    return $style;
}

function qcf_get_stored_reply( $id )
{
    $reply = get_option( 'qcf_reply' . $id );
    if ( !is_array( $reply ) ) {
        $reply = array();
    }
    $default = array(
        'replytitle'       => esc_html__( 'Message sent!', 'quick-contact-form' ),
        'replyblurb'       => esc_html__( 'Thank you for your enquiry, I&#146;ll be in contact soon', 'quick-contact-form' ),
        'sendcopy'         => '',
        'replycopy'        => '',
        'replysubject'     => esc_html__( 'Thank you for your enquiry', 'quick-contact-form' ),
        'replymessage'     => esc_html__( 'I&#146;ll be in contact soon. If you have any questions please reply to this email.', 'quick-contact-form' ),
        'messages'         => 'checked',
        'tracker'          => 'checked',
        'page'             => 'checked',
        'url'              => '',
        'subject'          => esc_html__( 'Enquiry from', 'quick-contact-form' ),
        'subjectoption'    => 'sendername',
        'qcf_redirect'     => '',
        'qcf_redirect_url' => '',
        'copy_message'     => esc_html__( 'Thank you for your enquiry. This is a copy of your message', 'quick-contact-form' ),
        'qcf_reload'       => '',
        'qcf_reload_time'  => '5',
        'bodyhead'         => esc_html__( 'The message is:', 'quick-contact-form' ),
    );
    $reply = array_merge( $default, $reply );
    return $reply;
}

function qcf_get_stored_error( $id )
{
    $error = get_option( 'qcf_error' . $id );
    if ( !is_array( $error ) ) {
        $error = array();
    }
    $qcf = qcf_get_stored_options( $id );
    $default = array(
        'field1'       => esc_html__( sprintf( 'Giving me %1$s would really help.', strtolower( $qcf['label']['field1'] ) ), 'quick-contact-form' ),
        'field2'       => esc_html__( sprintf( 'Please enter your %1$s address', strtolower( $qcf['label']['field2'] ) ), 'quick-contact-form' ),
        'field3'       => esc_html__( 'A telephone number is needed', 'quick-contact-form' ),
        'field4'       => esc_html__( sprintf( 'What is the %1$s', strtolower( $qcf['label']['field4'] ) ), 'quick-contact-form' ),
        'field5'       => esc_html__( 'Select an option from the list', 'quick-contact-form' ),
        'field6'       => esc_html__( 'Check at least one box', 'quick-contact-form' ),
        'field7'       => esc_html__( 'There is an error', 'quick-contact-form' ),
        'field8'       => esc_html__( sprintf( 'The %1$s is missing', strtolower( $qcf['label']['field8'] ) ), 'quick-contact-form' ),
        'field9'       => esc_html__( sprintf( 'What is your $1%s?', strtolower( $qcf['label']['field9'] ) ), 'quick-contact-form' ),
        'field10'      => esc_html__( 'Please select a date', 'quick-contact-form' ),
        'field11'      => esc_html__( 'Enter a value', 'quick-contact-form' ),
        'field13'      => esc_html__( 'Enter a value', 'quick-contact-form' ),
        'email'        => esc_html__( 'There&#146;s a problem with your email address', 'quick-contact-form' ),
        'telephone'    => esc_html__( 'Please check your phone number', 'quick-contact-form' ),
        'mathsmissing' => esc_html__( 'Answer the sum please', 'quick-contact-form' ),
        'mathsanswer'  => esc_html__( 'That&#146;s not the right answer, try again', 'quick-contact-form' ),
        'errortitle'   => esc_html__( 'Oops, got a few problems here', 'quick-contact-form' ),
        'errorblurb'   => esc_html__( 'Can you sort out the details highlighted below.', 'quick-contact-form' ),
        'emailcheck'   => '',
        'phonecheck'   => '',
        'consent'      => esc_html__( 'Consent is required before sending.', 'quick-contact-form' ),
        'spam'         => esc_html__( 'Your Details have been flagged as spam', 'quick-contact-form' ),
        'validating'   => esc_html__( 'Checking...', 'quick-contact-form' ),
        'sending'      => esc_html__( 'Sending message...', 'quick-contact-form' ),
        'redirecting'  => esc_html__( 'Redirecting...', 'quick-contact-form' ),
    );
    $error = array_merge( $default, $error );
    return $error;
}

function qcf_get_stored_setup()
{
    $qcf_setup = get_option( 'qcf_setup' );
    if ( !is_array( $qcf_setup ) ) {
        $qcf_setup = array();
    }
    $default = array(
        'current'     => '',
        'alternative' => '',
        'noui'        => '',
        'nostyling'   => '',
        'location'    => 'php',
        'nostore'     => '',
    );
    $qcf_setup = array_merge( $default, $qcf_setup );
    return $qcf_setup;
}

function qcf_get_stored_email()
{
    $qcf_email = get_option( 'qcf_email' );
    
    if ( !is_array( $qcf_email ) ) {
        $old_email = $qcf_email;
        $qcf_email = array();
        $qcf_email[''] = $old_email;
    }
    
    $default = array(
        '' => '',
    );
    $qcf_email = array_merge( $default, $qcf_email );
    return $qcf_email;
}

function qcf_get_stored_msg()
{
    $messageoptions = get_option( 'qcf_messageoptions' );
    if ( !is_array( $messageoptions ) ) {
        $messageoptions = array();
    }
    $default = array(
        'messageqty'   => 'fifty',
        'messageorder' => 'newest',
    );
    $messageoptions = array_merge( $default, $messageoptions );
    return $messageoptions;
}

function qcf_get_stored_autoresponder( $id )
{
    $auto = get_option( 'qcf_autoresponder' . $id );
    
    if ( !is_array( $auto ) ) {
        $send = qcf_get_stored_reply( $id );
        $qcfemail = qcf_get_stored_email();
        $fromemail = $qcfemail[$id];
        if ( empty($fromemail) ) {
            $fromemail = get_bloginfo( 'admin_email' );
        }
        $title = get_bloginfo( 'name' );
        
        if ( $send['sendcopy'] ) {
            $auto = array(
                'enable'    => $send['sendcopy'],
                'subject'   => $send['replysubject'],
                'message'   => $send['replymessage'],
                'sendcopy'  => $send['replycopy'],
                'fromname'  => $title,
                'fromemail' => $fromemail,
            );
            $send['thankyou'] = '';
            update_option( 'qcf_reply' . $id, $send );
            update_option( 'qcf_autoresponder' . $id, $auto );
        } else {
            $auto = array(
                'enable'    => '',
                'subject'   => esc_html__( 'Thank you for your enquiry.', 'quick-contact-form' ),
                'message'   => esc_html__( 'We will be in contact soon. If you have any questions please reply to this email.', 'quick-contact-form' ),
                'sendcopy'  => 'checked',
                'fromname'  => $title,
                'fromemail' => $fromemail,
            );
        }
    
    }
    
    return $auto;
}

function qcf_get_stored_emailmessage()
{
    $auto = get_option( 'qcf_emailmessage' );
    if ( !is_array( $auto ) ) {
        $auto = array();
    }
    $default = array(
        'subject'   => esc_html__( 'For your information', 'quick-contact-form' ),
        'message'   => esc_html__( 'Please respond to confirm your email address', 'quick-contact-form' ),
        'fromname'  => get_bloginfo( 'name' ),
        'fromemail' => get_bloginfo( 'admin_email' ),
    );
    $auto = array_merge( $default, $auto );
    return $auto;
}

function qcf_get_stored_emails( $id )
{
    $qcf_list = get_option( 'qcf_emails' . $id );
    if ( !is_array( $qcf_list ) ) {
        $qcf_list = array();
    }
    return $qcf_list;
}

function qcf_get_stored_redirect( $id )
{
    $qcf_redirect = get_option( 'qcf_redirect' . $id );
    
    if ( !is_array( $qcf_redirect ) ) {
        $qcf_redirect = array();
        $qcf_redirect['whichlist'] = 'dropdownlist';
    }
    
    return $qcf_redirect;
}
