<?php
/*
Plugin Name: More Privacy Options
Plugin URI:	http://wordpress.org/extend/plugins/more-privacy-options/
Description: WP3.0 multisite "mu-plugin" to add more privacy options to the options-privacy and ms-blogs pages. Sitewide "Users Only" switch at SuperAdmin-->Options page. Just drop in mu-plugins.
Version: 3.0.1
Author: D. Sader
Author URI: http://dsader.snowotherway.org/

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

Notes:
2.7 added a Sitewide "Users Only" switch to SiteAdmin-->Options, props Boonika
2.8 added action hook for writing robots.txt exclusions, props Zinj Guo 
	http://en.wikipedia.org/wiki/Robots.txt
2.9 tweaked for WPMU 2.7
2.9.1 added feed authentication so registered_users get feeds from feed readers such as Safari/Mail. Props: Greg
2.9.2 added "class"	ds_more_privacy_options to avoid function name collisions
2.9.3 added noindex, privacy_ping_filter, and do_robots fixes
3.0 added filter to admin header link, tweaked for WP3.0 multisite 
3.0.1 deprecated $user_level check replaced with is_user_logged_in()

TODO
To allow everyone who is on-campus into the blog, while requiring those off-campus to log in. Modify function ds_users_authenticator().

Like this:

if (     (strncmp('155.47.', $_SERVER['REMOTE_ADDR'], 7 ) == 0)  || (is_user_logged_in())                  ) {
        // user is either logged in or at campus 
                }
else {
        // user is either not logged in or at campus      

      if( is_feed() ) {
...
}

TODO protect files/attachments/images uploaded to protected blogs(.htaccess rewrites needed)
Pluginspiration: http://plugins.svn.wordpress.org/private-files/trunk/privatefiles.php

SSL Workaround
ctrl-alt-esc now trying this instead:
header('Location: ' . get_settings('siteurl') . '/wp-login.php?redirect_to=' . urlencode((is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
It appears to be working correctly on both domain and directory installs.
*/
class ds_more_privacy_options {

	function ds_more_privacy_options() {
	}

	function do_robots() {
		remove_action('do_robots', 'do_robots');

		header( 'Content-Type: text/plain; charset=utf-8' );

		do_action( 'do_robotstxt' );

		if ( '1' != get_option( 'blog_public' ) ) {
			echo "User-agent: *\n";
			echo "Disallow: /\n";
		} else {
			echo "User-agent: *\n";
			echo "Disallow:\n";
			echo "Disallow: /wp-admin\n";
			echo "Disallow: /wp-includes\n";
			echo "Disallow: /wp-login.php\n";
			echo "Disallow: /wp-content/plugins\n";
			echo "Disallow: /wp-content/cache\n";
			echo "Disallow: /wp-content/themes\n";
			echo "Disallow: /trackback\n";
			echo "Disallow: /comments\n";
		}
	}	
	function noindex() {
		remove_action( 'login_head', 'noindex' );
		remove_action( 'wp_head', 'noindex',1 );//priority 1

		// If the blog is not public, tell robots to go away.
		if ( '1' != get_option('blog_public') )
			echo "<meta name='robots' content='noindex,nofollow' />\n";
	}
	function privacy_ping_filter($sites) {
		remove_filter( 'option_ping_sites', 'privacy_ping_filter' );
		if ( '1' == get_option('blog_public') )
			return $sites;
		else
			return '';
	}
	//------------------------------------------------------------------------//
	//---Functions hooked into wpmu-blogs.php---------------------------------//
	//---TODO add messages to wpmu-blogs.php table----------------------------//
	function wpmu_blogs_add_privacy_options() { 
		global $details,$options;
		?>
		<h3 class="hndle"><span><?php _e( 'More Privacy Options' ); ?></span></h3>
			<input type='radio' name='blog[public]' value='1' <?php if( $details->public == '1' ) echo " checked"?>> <?php _e('Google-able') ?>&nbsp;&nbsp;
		<br />
	    	<input type='radio' name='blog[public]' value='0' <?php if( $details->public == '0' ) echo " checked"?>> <?php _e('No Google') ?> &nbsp;&nbsp;	    
		<br />
	    	<input type='radio' name='blog[public]' value='-1' <?php if( $details->public == '-1' ) echo " checked"?>> <?php _e('Network Registered Users Only') ?> &nbsp;&nbsp;
		<br />
	    	<input type='radio' name='blog[public]' value='-2' <?php if( $details->public == '-2' ) echo " checked"?>> <?php _e('Blog Members Only') ?> &nbsp;&nbsp;
		<br />
		    <input type='radio' name='blog[public]' value='-3' <?php if( $details->public == '-3' ) echo " checked"?>> <?php _e('Blog Admins Only') ?> &nbsp;&nbsp;
<p class="description"></p>		

		<?php
	}
	function wpmu_blogs_add_privacy_options_messages() {
		global $blog;
			if ( '1' == $blog[ 'public' ] ) {
				_e('Visible(1)');
			}
			if ( '0' == $blog[ 'public' ] ) {
				_e('No Search(0)');
			}
			if ( '-1' == $blog[ 'public' ] ) {
				_e('Users Only(-1)');
			}
			if ( '-2' == $blog[ 'public' ] ) {
				_e('Members Only(-2)');
			}
			if ( '-3' == $blog[ 'public' ] ) {
				_e('Admins Only(-3)');
			}
			echo '<br class="clear" />';
	}

	//------------------------------------------------------------------------//
	//---Functions hooked into blog privacy selector(options-privacy.php)-----//
	//------------------------------------------------------------------------//
	function add_privacy_options($options) { 
		global $current_site; 
		$blog_name = get_bloginfo('name', 'display');
?>
<br/>
			<input id="blog-private" type="radio" name="blog_public" value="-1" <?php checked('-1', get_option('blog_public')); ?> />
			<label for="blog-private"><?php _e('I would like my blog to be visible only to registered users of '); ?><?php echo esc_attr( $current_site->site_name ) ?></label>
<br/>
			<input id="blog-private" type="radio" name="blog_public" value="-2" <?php checked('-2', get_option('blog_public')); ?> />
			<label for="blog-private"><?php _e('I would like my blog to be visible only to <a href="users.php">registered users I add</a> to '); ?>"<?php echo $blog_name; ?>"</label>
<br/>
			<input id="blog-private" type="radio" name="blog_public" value="-3" <?php checked('-3', get_option('blog_public')); ?> />
			<label for="blog-private">I would like "<?php echo $blog_name; ?>" to be visible only to Admins.</label>
	<?php 
	}

	//------------------------------------------------------------------------//
	//---Functions for Registered Community Users Only Blog-------------------//
	//------------------------------------------------------------------------//
	function ds_users_authenticator () {
			if ( !is_user_logged_in() ) {
	      		if( is_feed()) {
    	       	$credentials = array();
        	   	$credentials['user_login'] = $_SERVER['PHP_AUTH_USER'];
        	   	$credentials['user_password'] = $_SERVER['PHP_AUTH_PW'];

       		    $user = wp_signon( $credentials );

	   	       if ( is_wp_error( $user ) )
    	       {
                header( 'WWW-Authenticate: Basic realm="' . $_SERVER['SERVER_NAME'] . '"' );
                header( 'HTTP/1.0 401 Unauthorized' );
                die();
    	       }
	       } else {
			nocache_headers();
			header("HTTP/1.1 302 Moved Temporarily");
			header('Location: ' . get_settings('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
        	header("Status: 302 Moved Temporarily");
			exit();
			}
		}
	}
	function registered_users_login_message () {
		echo '<p>';
		echo '' . bloginfo(name) . ' can be viewed by registered users of this community only.';
		echo '</p><br/>';
	}
	function registered_users_header_title () {
		global $current_site;
		echo 'Visible Only to Registered Users of '. esc_attr( $current_site->site_name );
	}
	function registered_users_header_link () {
		global $current_site;
		echo 'Visible Only to Registered Network Users';
	}

	//------------------------------------------------------------------------//
	//---Shortcut Function for logged in users to add timed "refresh"--------//
	//------------------------------------------------------------------------//
	function ds_login_header() {
			nocache_headers();
			header( 'Content-Type: text/html; charset=utf-8' );
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists('language_attributes') ) language_attributes(); ?>>
			<head>
				<title><?php _e("Private Blog Message"); ?></title>
				<meta http-equiv="refresh" content="5;URL=<?php echo get_settings('siteurl'); ?>/wp-login.php" />
				<?php wp_admin_css( 'css/login' );
				wp_admin_css( 'css/colors-fresh' );	?>				
				<link rel="stylesheet" href="css/install.css" type="text/css" />
				<?php do_action('login_head'); ?>
			</head>
			<body class="login">
				<div id="login">
					<h1><a href="<?php echo apply_filters('login_headerurl', 'http://' . $current_site->domain . $current_site->path ); ?>" title="<?php echo apply_filters('login_headertitle', $current_site->site_name ); ?>"><span class="hide"><?php bloginfo('name'); ?></span></a></h1>
	<?php
	}

	//------------------------------------------------------------------------//
	//---Functions for Members Only Blog---------------------------------------//
	//------------------------------------------------------------------------//
	function ds_members_authenticator () {
		if (( is_user_logged_in() ) && (!current_user_can('read'))) {
			$this->ds_login_header(); ?>
					<form name="loginform" id="loginform" />
						<p>Wait 5 seconds or 
							<a href="<?php echo get_settings('siteurl'); ?>/wp-login.php">click</a> to continue.</p>
							<?php $this->registered_members_login_message (); ?>
					</form>
				</div>
			</body>
		</html>
		<?php 
			exit();
		} elseif (!current_user_can('read')) {
			nocache_headers();
			header("HTTP/1.1 302 Moved Temporarily");
			header('Location: ' . get_settings('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
        	header("Status: 302 Moved Temporarily");
			exit();
		}
	}
	function registered_members_login_message () {
		echo '<p>';
		echo '' . bloginfo(name) . __(' can be viewed by members of this blog only.');
		echo '</p><br/>';
	}
	function registered_members_header_title () {
		echo __(' Visible only to users added to this blog');
	}
	function registered_members_header_link () {
		echo __(' Visible only to users added to this blog');
	}

	//-----------------------------------------------------------------------//
	//---Functions for Admins Only Blog--------------------------------------//
	//---WARNING: member users, if they exist, still see the backend---------//
	function ds_admins_authenticator () {
		if (( is_user_logged_in() ) && (!current_user_can('manage_options'))) {
			$this->ds_login_header(); ?>
						<form name="loginform" id="loginform" />
							<p>Wait 5 seconds or 
								<a href="<?php echo get_settings('siteurl'); ?>/wp-login.php">click</a> to continue.</p>
								<?php $this->registered_admins_login_message (); ?>
						</form>
					</div>
				</body>
			</html>
			<?php 
			exit();
		} elseif (!current_user_can('manage_options')) {
			nocache_headers();
			header("HTTP/1.1 302 Moved Temporarily");
			header('Location: ' . get_settings('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
        	header("Status: 302 Moved Temporarily");
			exit();
		}
	}
	function registered_admins_login_message () {
		echo '<p>';
		echo '' . bloginfo(name) . __(' can be viewed by administrators only.');
		echo '</p><br/>';
	}	
	function registered_admins_header_title () {
		echo __(' Visible Only to Admins - most privacy');
	}
	function registered_admins_header_link () {
		echo __(' Visible Only to Admins');
	}

//-----------------------------------------------------------------------//
//---Functions for SiteAdmins Options--------------------------------------//
//---WARNING: member users, if they exist, still see the backend---------//
	function sitewide_privacy_options_page() {
		$number = intval(get_site_option('ds_sitewide_privacy'));
		if ( !isset($number) ) {
			$number = '1';
		}
		echo '<h3>Network Privacy Selector</h3>';
		echo '
		<table class="form-table">
		<tr valign="top"> 
			<th scope="row">' . __('Blog Privacy') . '</th>';
			$checked = ( $number == "-1" ) ? " checked=''" : "";
		echo '<td><input type="radio" name="ds_sitewide_privacy" id="ds_sitewide_privacy" value="-1" ' . $checked . '/>
			<br />
			<small>
			' . __('Blog network can be viewed by registered users of this community only.') . '
			</small></td>';
			$checked = ( $number == "1" ) ? " checked=''" : "";
		echo '<td><input type="radio" name="ds_sitewide_privacy" id="ds_sitewide_privacy_1" value="1" ' . $checked . '/>
			<br />
			<small>
			' . __('Default: privacy managed per blog.') . '
			</small></td>
		</tr>
		</table>'; 
	}
	function sitewide_privacy_update() {
		update_site_option('ds_sitewide_privacy', $_POST['ds_sitewide_privacy']);
	}
}

if (class_exists("ds_more_privacy_options")) {
	$ds_more_privacy_options = new ds_more_privacy_options();	
	}

if (isset($ds_more_privacy_options)) {
//------------------------------------------------------------------------//
//---Hooks-----------------------------------------------------------------//
//------------------------------------------------------------------------//
// SupreAdmin->Options
add_action( 'update_wpmu_options', array(&$ds_more_privacy_options, 'sitewide_privacy_update'));
add_action( 'wpmu_options', array(&$ds_more_privacy_options, 'sitewide_privacy_options_page'));

// hooks into Misc Blog Actions in SuperAdmin->Sites->Edit
add_action('wpmueditblogaction', array(&$ds_more_privacy_options, 'wpmu_blogs_add_privacy_options'),999);
// hooks into Blog Columns views SiteAdmin->Blogs
// add_action('wpmublogsaction', array(&$ds_more_privacy_options, 'wpmu_blogs_add_privacy_options_messages') );

// hook into options-privacy.php Dashboard->Settings->Privacy.
add_action('blog_privacy_selector', array(&$ds_more_privacy_options, 'add_privacy_options'));

// all three add_privacy_option get a redirect and a message in the Login form
		$number = intval(get_site_option('ds_sitewide_privacy'));

if (( '-1' == $current_blog->public ) || ($number == '-1')) { // add exclusion of main blog if desired
	add_action('template_redirect', array(&$ds_more_privacy_options, 'ds_users_authenticator'));
	add_action('login_form', array(&$ds_more_privacy_options, 'registered_users_login_message')); 
	add_filter('privacy_on_link_title', array(&$ds_more_privacy_options, 'registered_users_header_title'));
	add_filter('privacy_on_link_text', array(&$ds_more_privacy_options, 'registered_users_header_link') );
	}
if ( '-2' == $current_blog->public ) {
	add_action('template_redirect', array(&$ds_more_privacy_options, 'ds_members_authenticator'));
	add_action('login_form', array(&$ds_more_privacy_options, 'registered_members_login_message')); 
	add_filter('privacy_on_link_title', array(&$ds_more_privacy_options, 'registered_members_header_title'));
	add_filter('privacy_on_link_text', array(&$ds_more_privacy_options, 'registered_members_header_link') );

}
if ( '-3' == $current_blog->public ) {
	add_action('template_redirect', array(&$ds_more_privacy_options, 'ds_admins_authenticator'));
	add_action('login_form', array(&$ds_more_privacy_options, 'registered_admins_login_message'));
	add_filter('privacy_on_link_title', array(&$ds_more_privacy_options, 'registered_admins_header_title'));
	add_filter('privacy_on_link_text', array(&$ds_more_privacy_options, 'registered_admins_header_link') );
}
// fixes robots.txt rules 
add_action('do_robots', array(&$ds_more_privacy_options, 'do_robots'),1);

// fixes noindex meta as well
add_action('wp_head', array(&$ds_more_privacy_options, 'noindex'),0);
add_action('login_head', array(&$ds_more_privacy_options, 'noindex'),1);

//no pings unless public either
add_filter('option_ping_sites', array(&$ds_more_privacy_options, 'privacy_ping_filter'),1);
}
?>