=== WordPress Form Manager ===
Contributors: hoffcamp
Donate link: http://www.campbellhoffman.com/
Tags: form, forms
Requires at least: 3.0.0
Tested up to: 3.1.1
Stable tag: 1.2.5

Put custom forms into posts and pages using shortcodes. Download submissions in .csv format.

== Description ==

Form Manager is a tool for creating forms to collect and download data from visitors to your WordPress site, and keeps track of time/date and registered users as well.  Form features include validation, requried fields, custom acknowledgments, and e-mail notifications.  Forms can be added to posts or pages using a simple shortcode format.  

Supported field types:

* text field
* text area
* dropdown
* radio buttons
* checkbox / checkbox list
* multiline select

Subtitles and notes can also be added to the form in any location.

If you are familiar with regular expessions, adding new validation types can be done quickly by editing the 'settings.php' file in the plugin's directory. 

*** I am starting work on version 2.  If you have suggestions or requests, please let me know! ***

== Installation ==

Method 1: Activate the 'WordPress Form Manager' plugin through the 'Plugins' menu in WordPress.  

Method 2: Download the source code for the plugin, and upload the 'wordpress-form-manager' directory to the '/wp-content/plugins/' directory.

== Frequently Asked Questions ==

= How do I create a new form? =

1. Click on the 'Forms' section, then find the 'Add New' button. A new form has just been created.
2. To edit the form, click on the name of the new form (usually 'New Form').

= How do I add the form to a post/page? = 

The shortcode is simple:

[form (shortcode)]

So if your form's shortcode is 'my-contact-form', the code would be:

[form my-contact-form]

= How do I add elements to the form? =

In the form editor (the title of the page should read 'Edit Form'), look for the text 'Insert Form Element:', and notice the buttons below.  By clicking any of these buttons, you will add this type of form element to the end of the form.

Make sure to click 'Save Form', found on the right (the blue button), when you are satisfied with your changes.

= How do I rearrange the elements of a form? = 

Rearranging form elements is done by drag and drop.  That is, click and hold anywhere on the form element you want to move (other than on the 'edit' and 'delete' buttons, of course), drag the form element up or down to the desired spot, and release the mouse button. 

= How do I edit a particular form element? =

Click on the 'edit' button within the particular form element.  Depending on the element type, this will display different options:

Text (single line text entry): 

* Label - This is the label that will be displayed with the form element.
* Default Value - For text inputs, this is the default text when the form is first loaded.
* Width (in pixels) - The width of the text field when displaying the form, in pixels.
* Required - Check this if you want the element to be required.  Required fields are displayed with a red asterisk (*) next to the label, and will not allow the user to submit data until something has been entered / selected for that element.
* Validation - This will require the user to input a particular type of input, such as an e-mail address or a phone number. NOTE: This is not the same as checking 'Required'; Validation will allow blank inputs. If you want the input to be required and validated, you must also check 'Required'.

Text Area (multi-line text entry):

* Label - This is the label that will be displayed with the form element.
* Default Value - For text inputs, this is the default text when the form is first loaded.
* Width / Height (in pixels) - The width and height of the textarea when displaying the form, in pixels. 
* Required - Check this if you want the element to be required.  Required fields are displayed with a red asterisk (*) next to the label, and will not allow the user to submit data until something has been entered / selected for that element.

Checkbox:

* Label - This is the label that will be displayed with the form element.
* Checked by Default - Whether or not the checkbox is checked by default when first displaying the form.

Separator (sub-title): 
* Label - This will be displayed as a sub-title. 

List: 

* Label - This is the label that will be displayed with the form element.
* Style - Lists can be displayed as a dropdown menu, a list of 'option' buttons, a list box, or a set of checkboxes.  Note that for the 'checkbox list' type, multiple selections are allowed. 
* Required - Check this if you want the element to be required.  Required fields are displayed with a red asterisk (*) next to the label, and will not allow the user to submit data until something has been entered / selected for that element.
* List Items - To add new items to the list, click 'Add'. To rearrange the options, click & drag the 'move' button next to the option. To delete an option, click the 'delete' button next to the option.  

Note: 

* Label - This is the label that will be displayed with the form element.
* Note - The note to be displayed. 

= Can users put HTML or JavaScript in my forms? =

No. All HTML tags are stripped from form inputs. 

= How do I add new validators? =

Inside the 'settings.php' file, use the 'fm_new_text_validator' function:

fm_new_text_validator($name, $label, $message, $regexp);

* $name - The 'slug' for the validator. Should only contain letters, dashes, and underscore.
* $label - The label of the validator in the 'Validation' dropdown for the Text element.
* $message - Message displayed for an invalid input. Should include one '%s', which will be repaced with the particular element's label when using the form. 
* $regexp - Regular expression, used in JavaScript's String.match() function. 

Refer to the 'settings.php' file for examples.

= What is the table structure for submission data? =

By default, all fields are of the type 'TEXT', and the timestamp is used as the primary key.  

The data tables are named '(wp prefix)fm_data_(form ID #)', where (wp prefix) is usually 'wp_', and the form ID can be found in the URL for editing a form, '(admin url)?page=fm-edit-form&<strong>id=1</strong>', or within the '(wp prefix)fm_forms' table. 

= Can I change the table structure for submission data? =

Certainly.  You can change field types and indexes.  If you need to restrict the length of a text input, use a custom validator as described above.  However, changing the primary key is not recommended, since duplicate entries cannot be checked with validation.

= The e-mail notification doesn't seem to work. Why? = 

Who knows. Form Manager uses the wp_mail() function, which according to the WordPress reference requires the following:

* Settings 'SMTP' and 'smtp_port' need to be set in your php.ini
* Also, either set the 'sendmail_from' setting in php.ini, or pass it as an additional header.

If you don't have access to php.ini, your best bet is to consult your host as to why your site can't send e-mails. 

== Upgrade Notice ==

= 1.2.6 =

Added e-mail notifications. 

= 1.2.5 =
Fixes multisite edit/data page bug. 

= 1.2.4 =
Fixes an installation error when starting with a fresh plugin install.

