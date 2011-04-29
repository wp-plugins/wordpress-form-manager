=== WordPress Form Manager ===
Contributors: hoffcamp
Donate link: http://www.campbellhoffman.com/
Tags: form, forms
Requires at least: 3.0.0
Tested up to: 3.1.1
Stable tag: 1.3.7

Put custom forms into posts and pages using shortcodes. Download submissions in .csv format.

== Description ==

Form Manager is a tool for creating forms to collect and download data from visitors to your WordPress site, and keeps track of time/date and registered users as well.  Form features include validation, required fields, custom acknowledgments, and e-mail notifications.  Forms can be added to posts or pages using a simple shortcode format.  

= Supported field types: =

* text field
* text area
* dropdown
* radio buttons
* checkbox / checkbox list
* multiline select
* reCAPTCHA

Subtitles and notes can also be added to the form in any location.


= Changes: =
= 1.3.7 =
* Fixed 'fm_settiings' table install error

= 1.3.6 =
* Advanced settings page
* Custom text validators using regular expressions

= 1.3.5 =
* E-mail notifications for registered users
* Admin and registered user e-mail notifications are now a global rather than per form setting.

= 1.3.4 =
* Added e-mail notification for user input (acknowledgment e-mail)
* Changed editor interface

= 1.3.3 =
* Adjusted for register_activation_hook() change
* Fixed some CSS style names likely to have conflicts

= 1.3.2 =
* Added reCAPTCHA field
* Added Settings page
* Multiple forms per page
* Fixed CSV data double quote bug
* Improved acknowledgement formatting

= 1.3.1 =
* Fixed 'Single submission' behavior bug
* Items in form editor update when 'done' is clicked
* Fixed list option editor bug

= 1.3.0 =
* Added form behaviors for registered users
* Cleaned up data page
* Added data summary to data page

= 1.2.10 =
* Rearranged editor sections
* Fixed checkbox list 'required' test
* Added single checkbox 'requried' test

= 1.2.9 = 
* Fixed .csv download bug

= 1.2.8 =
* Added e-mail notifications. 

= 1.2.5 =
* Fixes multisite edit/data page bug. 

= 1.2.4 =
* Fixes an installation error when starting with a fresh plugin install.


*** I am starting work on version 2.  If you have suggestions or requests, please let me know! ***

== Installation ==

Method 1: Activate the 'WordPress Form Manager' plugin through the 'Plugins' menu in WordPress.  

Method 2: Download the source code for the plugin, and upload the 'wordpress-form-manager' directory to the '/wp-content/plugins/' directory.

== Frequently Asked Questions ==

= How do I create a new form? =

1. Click on the 'Forms' section, then find the 'Add New' button. A new form has just been created.
2. To edit the form, click on the name of the new form (usually 'New Form').

= How do I add the form to a post/page? = 

The shortcode format is simple:

[form (slug)]

So if your form's slug is 'my-contact-form', the shortcode would be:

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

reCAPTCHA: 

* Label - This is the label that will be displayed with the form element.
 
= Can users put HTML or JavaScript in my forms? =

No. All HTML tags are stripped from form inputs. 

= How do I add new validators? =

Go to the 'Settings' page, then click on 'Advanced' in the upper right hand corner.  Under 'Text Validators', you can add / remove new validators for text fields.

* Label - The label of the validator in the 'Validation' dropdown for the Text element.
* Error Message - Message displayed for an invalid input. Should include one '%s', which will be repaced with the particular element's label when using the form. 
* Regular Expression - A regular expression, used in JavaScript's String.match() function.  You should include the '/' character at the beginning and end of the regular expression.

Refer to the 'settings.php' file for examples.

= What is the table structure for submission data? =

By default, all fields are of the type 'TEXT', and the timestamp is used as the primary key.  

The data tables are named '(wp prefix)fm_data_(form ID #)', where (wp prefix) is usually 'wp_', and the form ID can be found in the URL for editing a form, '(admin url)?page=fm-edit-form&<strong>id=1</strong>', or within the '(wp prefix)fm_forms' table. 

= Can I change the table structure for submission data? =

Certainly.  You can change field types and indexes.  If you need to restrict the length of a text input, use a custom validator as described above.  However, changing the primary key is not recommended, since duplicate entries cannot be checked with validation.

= Can I send e-mail acknowledgmements when somebody submits my form? =

Yes.  You first have to add a 'Text' field to your form, give it a label, set the validation to 'E-Mail'.  Now save the form.  Then, under 'E-Mail Notifications' (on the right hand side of the page), select the form item you just created under 'Send to (user entry)'.  Only text fields will show up in this list, and only saved fields as well.  If the field does not show up, make sure you have saved the form, then check again.  Although you can choose any text field, it is highly recommended that you set the validation for the field to 'E-Mail'.  Also, make sure to add a 'reCAPTCHA' to any forms you make public on your site.  Otherwise, a spam bot could cause your site to generate hundreds of e-mails... this is to be avoided!

= The e-mail notification doesn't seem to work. Why? = 

Who knows. Form Manager uses the wp_mail() function, which according to the WordPress reference requires the following:

* Settings 'SMTP' and 'smtp_port' need to be set in your php.ini
* Also, either set the 'sendmail_from' setting in php.ini, or pass it as an additional header.

If you don't have access to php.ini, your best bet is to consult your host as to why your site can't send e-mails. 

= What are the different form behaviors? =

First, any behavior other than 'Default' restricts the form to registered users of your site.

* Default - The form simply collects submission data. Anybody can use the form.
* Registered users only - The form is restricted to registered users of your site.  If an unregistered user / user that has not logged in accesses the form, a message is displayed: "(form name) is only available to registered users".  If you want to change the message, edit the line for $fm_registered_user_only_msg in settings.php .
* Single submission - The form can only be submitted once.  After that, a summary of the user's submission data is displayed in place of the form.
* 'User profile' style - Like 'Single submission', but allows users to edit the submitted data.  Only one submission is stored in the database.
* Keep only most recent submission - Behaves like 'Registered users only', but only keeps the latest submission in the database.

= How do I use / what is reCAPTCHA? =

The 'reCAPTCHA' form element is a spam / bot blocker.  It can be though of as a 'human test', that is, it distinguishes between human beings using your site and a spam bots.  It presents an image of some distorted text and asks you to enter the text.

To use a reCAPTCHA, simply insert one into your form - though you have to enter the reCAPTCHA API keys in the 'Settings' page under 'Forms' in WordPress.  

= How do I get reCAPTCHA API keys? =

As of this writing, go to www.google.com/recaptcha, click on 'USE reCAPTCHA ON YOUR SITE', then 'Sign up Now!', and follow the instructions.  You will be shown your 'public' and 'private' API keys; copy and paste these into the 'Settings' page under 'Forms' in WordPress.