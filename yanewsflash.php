<?php

/*
Plugin Name: YANewsflash Plugin
Plugin URI: http://www.stratos.me/wp-plugins/yanewsflash/
Description: Yet Another Newsflash (YANewsflash) is a plugin that gives you the ability to easily add a newsflash announcement on top of your home page (or any other page for that matter)
Author: stratosg
Version: 1.0.3
Author URI: http://www.stratos.me
*/

function yanewsflash_show(){
	global $yanewsflash_shown;
	$newsflash = get_option('yanewsflash_body');
	$newsflash_style = get_option('yanewsflash_style');
	
	if(yanewsflash_will_show() && !$yanewsflash_shown){
		?>
		<div style="<?php echo $newsflash_style; ?>">
			<?php echo stripslashes($newsflash); ?>
		</div>
		<?php
		$yanewsflash_shown = true;
	}
	
}

function yanewsflash_will_show(){
	$newsflash = get_option('yanewsflash_body');
	$newsflash_date = get_option('yanewsflash_date');
	$newsflash_times = get_option('yanewsflash_times');
	$newsflash_home = get_option('yanewsflash_home');
	$timestamp = strtotime($newsflash_date);
	$now = gettimeofday();
	
	if($now['sec'] <= $timestamp && $newsflash != '' && (!isset($_COOKIE[md5(get_option('yanewsflash_body'))]) || ($newsflash_times == 0) || (isset($_COOKIE[md5(get_option('yanewsflash_body'))]) && $_COOKIE[md5(get_option('yanewsflash_body'))] <= ($newsflash_times - 1))) && !is_feed()){
		if($newsflash_home != '' && is_home()){
			return true;
		}
		else if($newsflash_home == ''){
			return true;
		}
	}
	
	return false;
}

function yanewsflash_cookie_handle(){
	$newsflash_times = (isset($_COOKIE[md5(get_option('yanewsflash_body'))]) ? (intval($_COOKIE[md5(get_option('yanewsflash_body'))]) + 1) : 1 );
	setcookie(md5(get_option('yanewsflash_body')), $newsflash_times, time() + 3600);
	add_action('loop_start', 'yanewsflash_show');
}

add_action('init', 'yanewsflash_cookie_handle');

add_action('admin_menu', 'yanewsflash_admin_link');

function yanewsflash_admin_link(){
	add_options_page('YANewsflash', 'YANewsflash', 8, __FILE__, 'yanewsflash_admin');
}

function yanewsflash_admin(){
		global $wpdb;
		
		if(isset($_POST['yanewsflash_body'])){
			$newsflash = $_POST['yanewsflash_body'];
			$newsflash_date = $_POST['yanewsflash_date'];
			$newsflash_style = $_POST['yanewsflash_style'];
			$newsflash_times = $_POST['yanewsflash_times'];
			
			if($newsflash != ''){//it is set rather than unset
				update_option('yanewsflash_body', $newsflash);
				update_option('yanewsflash_date', $newsflash_date);
				update_option('yanewsflash_style', $newsflash_style);
				update_option('yanewsflash_times', $newsflash_times);
				update_option('yanewsflash_home', (isset($_POST['yanewsflash_home']) ? 'checked' : ''));
			}
			else{
				update_option('yanewsflash_body', '');
				update_option('yanewsflash_date', '');
				update_option('yanewsflash_times', '');
				update_option('yanewsflash_home', '');
			}
		}
		
		$timestamp = strtotime(get_option('yanewsflash_date'));
		$now = gettimeofday();
		$date_passed = (($now['sec'] > $timestamp || get_option('yanewsflash_body') == '') ? true : false);
		
	?>
	
	<h2>Newsflash</h2>
	<div>
		If you want a newsflash to appear on the homepage, set it up here. If you don't want anything there just leave this blank (red means it's deactivated whereis yellow means it's on).
	</div>
	<form action="?page=yanewsflash/yanewsflash.php" method="post">
		<table>
			<tr>
				<td valign="top">Body (can contain HTML)</td>
				<td><textarea style="background: <?php echo ($date_passed ? '#F07D7A' : '#FBFFC1'); ?>;" cols=75 rows=10 name="yanewsflash_body"><?php echo stripslashes(get_option('yanewsflash_body'));?></textarea></td>
			</tr>
			<tr>
				<td>Date (mm/dd/yyyy hh:mm)</td>
				<td><input style="background: <?php echo ($date_passed ? '#F07D7A' : '#FBFFC1'); ?>;" type="text" name="yanewsflash_date" value="<?php echo (get_option('yanewsflash_date') != '' ? get_option('yanewsflash_date') : date('m/d/Y H:i')); ?>"></td>
			</tr>
			<tr>
				<td>Show only in homepage</td>
				<td><input type="checkbox" name="yanewsflash_home" <?php echo get_option('yanewsflash_home'); ?>></td>
			</tr>
			<tr>
				<td>Show only</td>
				<td><input type="text" name="yanewsflash_times" value="<?php echo (get_option('yanewsflash_times') != '' ? get_option('yanewsflash_times') : '0'); ?>"> times (0 for always)</td>
			</tr>
			<tr>
				<td valign="top">Style (this is CSS and only edit if you know what you are doing)</td>
				<td><textarea rows=10 cols=75 name="yanewsflash_style"><?php echo get_option('yanewsflash_style');?></textarea></td>
			</tr>
			<tr>
				<td colspan=2><input type="submit" value="Save!"></td>
			</tr>
		</table>
	</form>
	
	<?php
}

//activation

function yanewsflash_activate(){
	$style = 'border: 2px solid black; background: #F8F899; margin: 5px; padding: 10px;';
	add_option('yanewsflash_style', $style);
	add_option('yanewsflash_body', '');
	add_option('yanewsflash_date', '');
	add_option('yanewsflash_home', '');
	add_option('yanewsflash_times', '');
}

function yanewsflash_deactivate() {
	delete_option('yanewsflash_style');
	delete_option('yanewsflash_body');
	delete_option('yanewsflash_date');
	delete_option('yanewsflash_home');
	delete_option('yanewsflash_times');
}

register_activation_hook(__FILE__, 'yanewsflash_activate');
register_deactivation_hook(__FILE__, 'yanewsflash_deactivate');

?>