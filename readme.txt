=== WordPress Form Manager ===
Contributors: hoffcamp
Donate link: http://www.campbellhoffman.com/
Tags: form, forms
Requires at least: 3.0.0
Tested up to: 3.1.1
Stable tag: 1.4.1

Put custom forms into posts and pages using shortcodes. Download submissions in .csv format.

== Description ==

Form Manager is a tool for creating forms to collect and download data from visitors to your WordPress site, and keeps track of time/date and registered users as well.  Forms are added to posts or pages using a simple shortcode format, or can be added to your theme with a simple API. 

= Features =
* validation
* required fields
* custom acknowledgments
* e-mail notifications.   

= Supported field types =

* text field
* text area
* dropdown
* radio buttons
* checkbox / checkbox list
* multiline select
* reCAPTCHA

Subtitles and notes can also be added to the form in any location.

= Publishing a Form =
Forms are placed within posts or pages.  For example, if your form's slug is 'form-1', put the following within a post or page: 

`[form form-1]`



= Changes: =
= 1.4.1 =
* Fixed saved bug

= 1.4.0 =
* Templates for e-mail notifications and form display, similar to WordPress theme functionality
* HTML 5 placeholders in supported browsers
* E-mail notification conflict with certain hosts
* Fixed 'list' option bug when creating a new list

= 1.3.15 =
* Fixed asterisks appearing below labels
* Fixed include bug with XAMPP

= 1.3.14 =
* Added reCAPTCHA color scheme option in settings
* Fixed conflict with other plugins using Google RECAPTCHA

= 1.3.13 =
* Changed upgrade mechanism

= 1.3.12 =
* Added 'required item message' to form editor
* Fixed upgrade from 1.3.3 and older

= 1.3.11 =
* Full Unicode support
* Added date validator for text fields

= 1.3.10 =
* Added API stable fm_doFormBySlug($formSlug) to show forms within templates
* Admin can change plugin's shortcode in 'Advanced Settings'

= 1.3.9 =
* Fixed form behavior selection bug

= 1.3.8 =
* Fixed possible style conflict with Kubric (Default) theme

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

`[form (slug)]`

So if your form's slug is 'my-contact-form', the shortcode would be:

`[form my-contact-form]`

= How do I add elements to the form? =

In the form editor (the title of the page should read 'Edit Form'), look for the text 'Insert Form Element:', and notice the buttons below.  By clicking any of these buttons, you will add this type of form element to the end of the form.

Make sure to click 'Save Form', found on the right (the blue button), when you are satisfied with your changes.

= How do I rearrange the elements of a form? = 

Rearranging form elements is done by drag and drop.  That is, click and hold anywhere on the form element you want to move (other than on the 'edit' and 'delete' buttons, of course), drag the form element up or down to the desired spot, and release the mouse button. 

= How do I edit a ____ form element? =

Click on the 'edit' button within the particular form element.  Depending on the element type, this will display different options:

Text (single line text entry): 

* Label - This is the label that will be displayed with the form element.
* Placeholder - This will appear within the text field as a placeholder until the user inputs something.
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


= What is the table structure for submission data? =

By default, all fields are of the type 'TEXT', and the timestamp is used as the primary key.  

The data tables are named '(wp prefix)fm_data_(form ID #)', where (wp prefix) is usually 'wp_', and the form ID can be found in the URL for editing a form, '(admin url)?page=fm-edit-form&<strong>id=1</strong>'.  The ID for a form can also be found in the '(wp prefix)fm_forms' table.

= Can I change the table structure for submission data? =

Certainly.  You can change field data types and indexes (but not primary keys) without worrying about breaking the plugin.  If you need to restrict the length of a text input, use a custom validator as described above.  However, changing the primary key is not recommended, unless you are certain there will not be a duplicate entry for this field, or are willing to live with the consequences (a MySQL error).

= Can I send e-mail acknowledgements to the person who submits my form? =

Yes.  You first have to add a 'Text' field to your form, give it a label, set the validation to 'E-Mail'.  Now save the form.  Then, under 'E-Mail Notifications' (on the right hand side of the page), select the form item you just created under 'Send to (user entry)'.  Only text fields will show up in this list, and only saved fields as well.  If the field does not show up, make sure you have saved the form, then check again.  Although you can choose any text field, it is highly recommended that you set the validation for the field to 'E-Mail'.  

Also, make sure to add a 'reCAPTCHA' to any forms you make public on your site.  Otherwise, a spam bot could cause your site to generate truckloads of e-mails... this is to be avoided!

= The e-mail notification doesn't seem to work. Why? = 

Who knows. Form Manager uses the wp_mail() function, which according to the WordPress reference requires the following:

* Settings 'SMTP' and 'smtp_port' need to be set in your php.ini
* Also, either set the 'sendmail_from' setting in php.ini, or pass it as an additional header.

If you don't have access to php.ini, your best bet is to consult your host as to why your site can't send e-mails. 

= What are the different form behaviors? =

First, any behavior other than 'Default' restricts the form to registered users of your site.

* Default - The form simply collects submission data. Anybody can use the form.
* Registered users only - The form is restricted to registered users of your site.  If an unregistered user / user that has not logged in accesses the form, a message is displayed: "(form name) is only available to registered users".
* Single submission - The form can only be submitted once.  After that, a summary of the user's submission data is displayed in place of the form.
* 'User profile' style - Works like 'Single submission', but allows users to edit the submitted data.  Only one submission is stored in the database.
* Keep only most recent submission - Behaves like 'Registered users only', but only keeps the latest submission in the database.

= What is reCAPTCHA? =

The 'reCAPTCHA' form element is a spam / bot blocker.  It can be though of as a 'human test', that is, it distinguishes between human beings using your site and a spam bots.  It presents an image of some distorted text and asks you to enter the text.

To use a reCAPTCHA, simply insert one into your form - though you have to enter the reCAPTCHA API keys in the 'Settings' page under 'Forms' in WordPress.  

= How do I get reCAPTCHA API keys? =

As of this writing, go to www.google.com/recaptcha, click on 'USE reCAPTCHA ON YOUR SITE', then 'Sign up Now!', and follow the instructions.  You will be shown your 'public' and 'private' API keys; copy and paste these into the 'Settings' page under 'Forms' in WordPress.

= Is there an API? =

The API only has a single function, fm_doFormBySlug(), that takes a single parameter, a string containing the slug of a form.  For example, if your form's slug is 'form-1', you would put the following in your code:

`echo fm_doFormBySlug('form-1');`

= How do I customize the e-mail notification? = 

See 'Creating a summary template'.  The process is the same for summary templates as for e-mail templates. 

= Creating a summary template =

As of version 1.4.0, you can use a template for e-mail notifications.  The mechanism is similar to wordpress theming.  You should first examine the default template, 'fm-summary-default.php', located in '/templates' in the plugin's directory (usually /wp-content/plugins/wordpress-form-manager).  

Step 1: To make a new template, make a new file in the '/templates' directory, within the plugin's main directory.  The first few lines of the template must look like the following:

`<?php /*
Template Name: (Template name goes here)
Template Description: (A brief description of the template goes here)
Template Type: (either 'email', 'summary', or 'email, summary', to specifiy what the template should be used for)
*/ ?>`

* Template Name - (required) This is what will appear in the menu to select the template for use by your form.
* Template Description - This should be a short description of the template.  This won't show up anywhere, but you should include it anyway.
* Template Type - (required) This must be the word(s) 'email' and/or 'summary', separated by a comma if both are included.  This specifies whether the template is an e-mail notification template, data summary template, or both.

Step 2: As a bare minimum, you need to output the names of the form elements and the data that was submitted.  The mechanism for doing this is very similar to the way wordpress themes display posts.  Below is an example of how to display the form submission as an unordered list:

`<ul>
<?php while(fm_summary_have_items()): fm_summary_the_item(); ?>
<li><?php echo fm_summary_the_label();?>: <?php echo fm_summary_the_value();?></li>
<?php endwhile; ?>
</ul>`

If you add the code above below the required code mentioned in step 1, you already have a working summary / e-mail notification template.  Congratulations!

Step 3: Add a formatted timestamp somewhere.  This is especially helpful to give some context to the submitted data.  To add the timestamp, you could simply use: 

`<?php echo fm_summary_the_timestamp(); ?>`

This will display something like "2011-05-19 01:21:03", which unfortunately is not the most user friendly way to display time and date.  The best way is to use php's date() and strtotime() functions in conjunction with fm_summary_the_timestamp(), such as the following:

`<?php echo date("M j, Y @ g:i A", strtotime(fm_summary_the_timestamp())); ?>`

This will look something like "May 19, 2011 @ 1:21 AM", which is much more user friendly.  If you want to fiddle with the date format, you should look up date() at php.net for information on how the format string works.  

Step 4: Display some information about the user.  Of course, this only applies to users that are logged in to your site.  To check if this is the case, you only need to check if fm_summary_the_user() is equal to "".  Below is an example:

`<?php 
$userName = fm_summary_the_user(); 
if($userName != ""){
	//stuff to do if there is a logged in user
}
?>`

Once you have detected a logged in user, you can use any of wordpress's functions to extract information about that user, such as get_userdatabylogin().  Below is an example:

`$userData = get_userdatabylogin($userName);
echo "Submitted by: <strong>".$userData->last_name.", ".$userData->first_name."</strong>";`

Step 5: Add a friendly message, your company logo, or whatever else.  At this point, we have covered everything that happens in the default summary template.  Changes you might want to play with would be converting the unordered list in step 2 to a table, or perhaps add classnames to the HTML to fit with your theme better.  To use your new template, either choose it from the list of templates in the 'Advanced' settings to apply the template to all forms, or choose the template within the form editor to apply the template to a single form. 

Below is a complete list of the functions available for use within a summary template: 

fm_summary_the_title() - the title of the form
fm_summary_have_items() - works like have_posts() for wordpress themes.  Returns 'true' if there are more items left in the form.
fm_summary_the_item() - works like the_item() for wordpress themes.  Initializes the current form item.
fm_summary_the_label() - label of the current form item
fm_summary_the_value() - submitted value of the current form item
fm_summary_the_timestamp() - timestamp for the current submission
fm_summary_the_user() - the login name for the current user.  If no user is logged in, this returns an empty string.

= Creating a form template =

The form template system is similar to the wordpress theme sytem.  You should first examine the default template, 'fm-form-default.php',  located in '/templates' in the plugin's directory (usually /wp-content/plugins/wordpress-form-manager).

Step 1: To make a new template, make a new file in the '/templates' directory, within the plugin's main directory.  The first few lines of the template must look like the following:

`<?php /*
Template Name: (Template name goes here)
Template Description: (A brief description of the template goes here)
Template Type: form
*/ ?>`

* Template Name - (required) This is what will appear in the menu to select the template for use by your form.
* Template Description - This should be a short description of the template.  This won't show up anywhere, but you should include it anyway.
* Template Type - (required) This must be 'form' for a form template. 

Step 2: First, every form template needs appropriate open and close <form> tags.  This can be done in two ways:

`<?php fm_form_start(); ?>`

or

`<form class="<?php echo fm_form_class();?>" method="post" action="<?php echo fm_form_action();?>" name="<?php echo fm_form_ID();?>" id="<?php echo fm_form_ID();?>" >`

The second method gives you more freedom, the first method is simpler.  Either way, you must closeout the form:

`<?php echo fm_form_end(); ?>`

or

`</form>`

Either way works, and for now, they are equivalent.  To keep track of the template so far, we have:

`<?php /*
Template Name: (Template name goes here)
Template Description: (A brief description of the template goes here)
Template Type: form
*/ ?>
<?php fm_form_start(); ?>

<?php fm_form_end(); ?>`

Step 3: The next step is to create the main loop.  This will display the form items.  For this example, and for the default template, we will use an unordered list, but you could use a table, divs, or whatever you can think of instead:

`<ul>
	<?php while(fm_form_have_items()): fm_form_the_item(); ?>
	<li>
		<label><?php echo fm_form_the_label(); ?><?php if(fm_form_is_required()) echo "&nbsp;<em>*</em>"; ?></label>
		<?php echo fm_form_the_input(); ?>		
	</li>
	<?php endwhile; ?>
</ul>`

The mechanism is similar to a wordpress theme.  The fm_form_have_items() function tests if there are more items to display, and fm_form_the_item() loads the next item to be displayed.  The code between the while() and endwhile is repeated for each form item - you will notice that this loop creates <li> tags for each form item.  The current item's label is given by fm_form_the_label(), and the form's input is given by fm_form_the_input().  

You should test to see if the item is set as 'required', and if so, output an asterisk next to the label.  This is done by the statement:

`<?php if(fm_form_is_required()) echo "&nbsp;<em>*</em>"; ?>`

So far, we have the following for our template:

`<?php /*
Template Name: (Template name goes here)
Template Description: (A brief description of the template goes here)
Template Type: form
*/ ?>

<?php fm_form_start(); ?>

<ul>
	<?php while(fm_form_have_items()): fm_form_the_item(); ?>
	<li>
		<label><?php echo fm_form_the_label(); ?><?php if(fm_form_is_required()) echo "&nbsp;<em>*</em>"; ?></label>
		<?php echo fm_form_the_input(); ?>		
	</li>
	<?php endwhile; ?>
</ul>

<?php fm_form_end(); ?>`

Step 4: Now you need to add the submit button.  There are two ways to do this: 

`<?php echo fm_form_the_submit_btn(); ?>`

or

`<input type="submit" name="<?php echo fm_form_submit_btn_name();?>" class="submit" value="<?php echo fm_form_submit_btn_text();?>" onclick="return <?php echo fm_form_submit_btn_script();?>" />` 

As with the form tag, the first method is simpler, and the second gives you more freedom.  We will skip the recap of the code so far, and head to the next step. 

Step 5: The last piece of required code for a form template is as follows:

`<?php echo fm_form_hidden(); ?>`

This must be placed just before the end of the form (just before fm_form_end() or </form>).  This contains the scripts required to make the validation and some other features function properly.  Below is what our code looks like so far:

`<?php /*
Template Name: (Template name goes here)
Template Description: (A brief description of the template goes here)
Template Type: form
*/ ?>

<?php fm_form_start(); ?>

<ul>
	<?php while(fm_form_have_items()): fm_form_the_item(); ?>
	<li>
		<label><?php echo fm_form_the_label(); ?><?php if(fm_form_is_required()) echo "&nbsp;<em>*</em>"; ?></label>
		<?php echo fm_form_the_input(); ?>		
	</li>
	<?php endwhile; ?>
</ul>

<?php echo fm_form_the_submit_btn(); ?>

<?php echo fm_form_hidden(); ?>
<?php fm_form_end(); ?>`

Step 6: At this point, you will want to make the form look more user friendly.  For example, you may want to add the title of the form somewhere.  This can be done with the following code:

`<?php echo fm_form_the_title(); ?>`

To use your new template, either choose it from the list of templates in the 'Advanced' settings to apply the template to all forms, or choose the template within the form editor to apply the template to a single form.  You will notice that there are no longer any 'Appearance' options within the form editor.  This is because these options are part of the default template.  To make your own options, see the tutorial, 'Adding options to a template'

= Add options to a template =

This tutorial assumes that you already have a form template that you want to add options to.  First, options are added at the top of the template, just below 'Template Type'.  You should examine the default template (fm-form-default.php) to get an idea of what is involved in specifying an option.  The very first line in specifying an option looks like the following:

`option: $varName, (type)`

Here, $varName is the php variable you want the option's value to be loaded into, and 'type' is either 'text', 'checkbox', or 'select'.  The next part of the specification is the label:

`label: (Option label goes here)`

This will appear next to the option in the form editor.  You can also add a description:

`description: (short description goes here)`

The next thing to specify will be the default value.  If your option is of 'text' type, then this can be any text value.  If the item is a 'checkbox', then you only need to specify something if you want the checkbox to be checked by default; in this case, you would specify the default value to be 'checked'.  Here is an example of a fully formed 'text' option:

`option: $textVar, text
	label: Enter some text
	description: This is where you would enter some text
	default: Hello there`

And a fully formed 'checkbox' option, that is checked by default:

`option: $checkboxVar, checkbox
	label: Do the thing? 
	description: Check the box if you want to do the thing
	default: checked`

If your option is a 'select' type, then you must specify the options to choose from, and the values associated with those options.  For example, you might have options like "This is the first option" and "This is the second option", but you only want to store the values "option1" and "option2" in your variable.  The following shows you how to specify the options for a 'select' type:

`options: 'option1' => "This is the first option", 'option2' => "This is the second option"`

Below is a fully formed 'select' type, where the second option is set as the default:

`option: $selectVar, select
	label: Choose an option
	description: Seriously, just pick something
	options: 'option1' => "This is the first option", 'option2' => "This is the second option"
	default: option2`