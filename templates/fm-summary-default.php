<?php
/*
Template Name: Form Manager Default
Template Description: The default template for e-mail notifications and summaries
Template Type: email, summary

//////////////////////////////////////////////////////////////////////////////////////////

Below are the functions that can be used within a summary template:

fm_summary_the_title() - the title of the form
fm_summary_have_items() - works like have_posts() for wordpress themes.  Returns 'true' if there are more items left in the form.
fm_summary_the_item() - works like the_item() for wordpress themes.  Initializes the current form item.
fm_summary_the_label() - label of the current form item
fm_summary_the_value() - submitted value of the current form item
fm_summary_the_timestamp() - timestamp for the current submission
fm_summary_the_user() - the login name for the current user.  If no user is logged in, this returns an empty string.
fm_summary_the_ip() - the IP address of the user who submitted the form.

*NOTE: 'Summary' templates can also be used for e-mails.  Notice that under 'Template Type', both 'email' and 'summary' are listed.  If you want to make a template for e-mail notifications only, then you should only put 'email' under 'Template Type'.

//////////////////////////////////////////////////////////////////////////////////////////

*/
?>
<?php /* The title of the form */ ?>
<h2><?php echo fm_summary_the_title(); ?></h2>

<?php /* The user's first and last name, if there is a logged in user */ ?>
<?php 
$userName = fm_summary_the_user(); 
if($userName != ""){
	$userData = get_userdatabylogin($userName);
	echo "Submitted by: <strong>".$userData->last_name.", ".$userData->first_name."</strong><br />";
}
?>

<?php /* The time and date of the submission.  Look up date() in the PHP reference at php.net for more info on how to format timestamps. */ ?>
On: <strong><?php echo date("M j, Y @ g:i A", strtotime(fm_summary_the_timestamp())); ?></strong> <br />
IP: <strong><?php echo fm_summary_the_IP(); ?>
<?php /* The code below displays each form element, in order, along with the submitted data. */ ?>
<ul>
<?php while(fm_summary_have_items()): fm_summary_the_item(); ?>
<li><?php echo fm_summary_the_label();?>: <strong><?php echo fm_summary_the_value();?></strong></li>
<?php endwhile; ?>
</ul>