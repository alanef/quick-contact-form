=== Quick Contact Form ===

Contributors: 
Tags: contact form
Requires at least: 4.0
Tested up to: 5.4
Stable tag: 6.19

A simple plug and play contact form with a huge range of options and styles.

== Description ==

A really, really simple GDPR compliant drag and drop contact form. There is nothing to configure, all you have to do is set up your email address and add the shortcode to your pages.

= Features =

*	Drag and drop fields with easy to edit labels and captions
*	Massive range of built in styles with custom CSS support
*	Display and download messages
*	Custom error and thank-you messages
*	Multiple form support
*   Akismet spam filtering
*   Fully editable autoresponder
*   GDPR compliant

= Upgrade to Pro =

*   Create your own mailing lists
*   Send emails from the plugin
*   View and access emailed attachments
*   Mailchimp integration coming soon

= Developers plugin page =

[quick contact form plugin](http://quick-plugins.com/quick-contact-form/).

== Screenshots ==

1.	This is main admin screen.
2.	This is an example of multiple forms on a single page. Everything on the form is editable.
3.	This is the messages screen (a copy of all the emails sent from the form).

== Installation ==

1.	Login to your wordpress dashboard.
3.	Go to 'Plugins', 'Add New' then search for 'Quick Contact Form'.
4.	Follow the on screen instructions.
5.	Activate the plugin.
6.	Go to the plugin 'Settings' page to edit your email address.
7.	Edit any of the form settings if you wish.
8.	Drag and drop the 'Quick Contact Form' widget to your sidebar.
9.	To use the form in your posts and pages add the shortcode `[qcf]`.
10.	To use the form in your theme files use the code `<?php echo do_shortcode('[qcf]'); ?>`.

If you wany more than on form on your site just create a new form on the setup page and save.

== Frequently Asked Questions ==

= How do I change the labels and captions? =
Go to your plugin list and scroll down until you see 'Quick Contact Form' and click on 'Settings'. Select the 'Form Settings', 'Error Messages' or 'Reply options' tabs. Change the settings and Save (quite important this last bit).

= What's the shortcode? =
[qcf]

= How do I change the colours? =
Go to the plugin settings page and click on styling 

= Can I add more fields? =
No.

= Why not? =
Well OK yes you can add more fields if you want but you are going to have to fiddle about with the php files which needs a bit of care an attention. Everything you need to know is in the [wordpress codex](http://codex.wordpress.org/Writing_a_Plugin).

= It's all gone wrong! =
If it all goes wrong, just reinstall the plugin and start again. If you need help then [contact me](http://quick-plugins.com/contact-me/).

= Does anybody read these things? =
I did, and so have you.  Maybe there will be more, who knows.

= Have you got any pictures of cute kittens? =
No but I've inherited 2 ancient, deaf and grumpy Persians.

== Changelog ==

= 6.20 = 
*   Removed Mime from message headers

= 6.19 =
*   Added Gutenberg block
*   Fixed email builder in message centre
*   Revised email header formatting

= 6.18 =
*   Data storage consent option
*   No data retention option
*   Active Campaign option

= 6.17 =
*   PHP7 bug fix

= 6.16 = 
*   Upgrade now has Mailchimp integration
*   Multiple redirection options

= 6.15 = 
*   Added an upgrade option
*   Removed depreciated WP function

= 6.14 = 
*   Option as to where the custom CSS load: external stylesheet or in document head

= 6.13 = 
*   Minor bug fixes

= 6.12 =
*   Option to select email address for messages report
*   Styles no longer loaded in head
*   Datepicker uses WP localizer
*   Thickness and handle corner styles for the slider
*   Submit button hover styles
*   Styling option for input margins and padding

= 6.11 =
*   Option to require an attachment
*   Validation and transmission without page reloads
*   Slider width styles
*   Improved autoresponder
*   Better style handling

= 6.10.2 =
*   Autofill option
*   Bug fix for attachment uploads
*   Improved attachments filename filter

= 6.10.1 = 
*   Widget update for WP 4.3

= 6.10 = 
*   New Autoresponder
*   Updated header styling options
*   Code optimisation
*   Minor bug fixes

= 6.9.5 =
*   Option to remove styling
*   CSS Tweak to prevent double line spacing
*   CSRF and XSS security patches

= 6.9.4 =
*   Bug fix on the telephone number validation
*   Tweak to Akismet functions

= 6.9.3 =
*   Stylesheet bug fix.
*   Updated javascript for range slider

= 6.9.2 =
*   New range slider field
*   Akismet bug fix
*   Admin notice bug fix
*   Page now reloads to form location not top of page
*   Removed captcha column from messages report

= 6.9.1 =
*   Option to delete individual messages
*   On submit now returns page to the form location

= 6.9 =
*   Now with Aksimet validation
*   Locale scripts for dates
*   Fixed captcha styling bug
*   Better styling support for submit images

= 6.8.3 =
*   Added styling options for the Error messages
*   Fixed line height bug (that has been bugging me for ages)
*   Fixed validation error on selector fields

= 6.8.2 =
*   Added error message styles
*   fixed broken link to CSS editor

= 6.8.1 =
*   Bug fix to the multiuse selector fields
*   fixed broken link to CSS editor

= 6.8 =
*   Added second multi use field
*   Changed selector fields to mulit use fields 

= 6.7 =
*   Added new multi use field
*   Made Captcha a selectable field
*   Added new styles for input fields
*   fixed minor bugs (including the wrong shortcode)

= 6.6.2 =
*	Fix so submit buttons now work properly

= 6.6.1 =
*	Bug fix

= 6.6 =
*	Submit button border styling
*	Allow background images
*	Moved selector options to the form field settings
*	Added delete buttons to the setup page
*	Fixed a bug in the submit button image styles
*	Fixed a formatting bug on the messages page
*	Made the settings pages a lot more pretty (and easier to use)

= 6.5.1 =
*	Jquery bug fix

= 6.5 =
*	Added SMTP option
*	Styles now have color pickers
*	Fixed the line height problem for some themes.
*	Improved Wordpress 3.8 styling

= 6.4 =
*	More options for the thank-you messages
*	Wordpress 3.8 compatible

= 6.3 =
*	Closed an XSS security hole
*	Fixed some styling issue on the error messages.
*	Added formating options for textarea
*	Added more styling options for the submit button
*	Added clickable lablels for radio and checkbox fields

= 6.2 =
*	Fixed a complete cock up on the message styling

= 6.1 =
*	Small bug fix to stop duplicate info being sent.
*	Improved the way fonts are managed
*	Added styling options for the submit button
*	Added XSS filters
*	Custom CSS now loads as an external file

= 6.0 =
*	Whole new message display and download option.
*	Fixed a bug that displayed empty message fields

= 5.7 =
*	More options when you send the form.

= 5.6 =
*	New form field. Adds a date picker to your forms.

= 5.5 =
*	Option to select mail function

= 5.4 =
*	Added dropdown to sidebar widget so you can select named forms

= 5.3 =
*	Changed the order of the columns in the form settings page
*	Made it easier to swap between named forms in the settings pages
*	Fixed some spellings errors
*	Bug fix: you can now add multiple email addresses on a fresh install

= 5.2.1 =
*	Bug fix: wp_mail chopped the last character of the senders name off!

= 5.2 =
*	Changed to wp_mail to fix webhosts blocking gmail and other webmail services
*	Changed language to UTF-8
*	Code tweaks

= 5.1 =
*	Reset buttons on each page
*	Simple validation of form names (replaces spaces with hyphens)
*	Code tweaks

= 5.0 = 
*	Multiple forms! Something that has been a long time coming.

= 4.5 =
*	Put all the admin functions in a seperate file (700 lines of code less for your visitors to load).
*	Added options to change the fonts and field borders.
*	Moved the styles to the documment head (no longer inline).
*	Improved the tracking options.

= 4.4 & 4.4.1 =
*	Bug fixes in the CSS
*	Tweaked the admin code to make the UI a bit less sensitive to user settings

= 4.3 =
*	Added option for 100% width (for responsive themes)

= 4.2 =
*	Cleaned out a whole load of code - file is now 6Kb smaller.
*	Rewritten the help files
*	Tweaked the validation function

= 4.1 = 
*	Fixed a bug in file attachment processing
*	Added stripslashes to the admin fields (a few had got missed)

= 4.0 =
*	Option to send file attachments.
*	Custom CSS support.
*	Allow redirection after message has been sent.
*	Reworked the 'form settings' instructions and added drag and drop arrows.
*	Direct links to the CSS and PHP editors from the appropriate setting page.
*	A lot of coding changes to speed up form processing.
*	As soon as the last few users upgrade from Version 2 I can remove about 100 lines of code!

= 3.2 and 3.3.1 =
*	Validation can check the email and telephone number format even if they are not required fields.
*	Missing apostrophe added (bugfix).

= 3.1 =
*	Random maths captcha added.
*	Editable email subject line.
*	Background colour options.
*	Changed the layout of the form editor to make it simpler.
*	Improved the the instructions for use.
*	Fixed a small bug in the checkbox validator.

= 3.0 =
*	Now lots of fields to select and arrange.
*	Changed the way files are written to the database.

= 2.5 =
*	Added tab to edit the error messages.

= 2.4 =
*	Added options to edit the thank you message.
*	Added options to display and track messages.

= 2.3 =
*	Added options to select which fields you want on your form.
*	Added a forth field so you can have email and telephone number (woo!)
*	Tidied up the way messages are displayed.
*	Fixed sorting bug.

= 2.2 =
*	Added reset options.

= 2.1 =
*	Tweak to the stylesheet to cope with the hopeless Internet Explorer CSS support.

= 2.0 =
*	Major upgrade to the settings interface.
*	Option to display latest messages on your dashboard.

= 1.4 =
*	Added sidebar widget.
*	Changed text colour of required fields (looks much nicer).

= 1.3 =
*	Added an optional maths checker to catch the spambots.
*	Added email and telephone number validation.

= 1.2 =
*	Changed the way error and the thank you messages are displayed (no more popups).
*	Added option to select which fields are required.

= 1.1 =
*	Fixed 'onclick' bug

= 1.0 =
*	Initial Issue
