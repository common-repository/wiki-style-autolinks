<?PHP

/* 
  Plugin Name: Wiki-style Autolinks
  Plugin URI: http://wpEduSuite.cole20.com/wiki-style-autolinks
  Description: Link automatically to any post or page on your site with the syntax [A:page-title] or [A:post-title]. 
  Version: 0.2
  Author: Carlos Ruiz
  Author URI: http://www.cole20.com/
*/

/* 
  Copyright 2008 Carlos Ruiz (email: carlosnoal@yahoo.es)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/


/* Adding plugin if not already exists function */ 

if (function_exists('cf_wikiterms')) 
{
	add_filter('the_content', 'cf_wikiterms');

	/* Adding parameters */
	
	
	add_option('cf_wikiterms_style', '1', 'switch to activate formatting of link');
	add_option('cf_wikiterms_rel','0','activate or deactivate rel=nofollow in hyperlink');

	/* Adding menu and parameters page */
	add_action('admin_menu', 'cf_wikiterms_admin_menu');
	
	/* Loading text domain */
	load_plugin_textdomain('wiki-style-autolinks', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/languages', dirname(plugin_basename(__FILE__)).'/languages');
}


/* Adding parameters page */

function cf_wikiterms_admin_menu()
{
	add_submenu_page('plugins.php', 'Wiki-style Autolinks Plugin Options', 'Wiki-autolinks', 5, basename(__FILE__),'cf_wikiterms_options_page'); 
}


/* Options page */

function cf_wikiterms_options_page() {

    $hidden_field_name = 'mt_submit_hidden';
    
   
   
    $opt_name_style = 'cf_wikiterms_style';
    $opt_name_rel = 'cf_wikiterms_rel';

    
  
   
    $data_field_name_style = 'cf_wikiterms_style';
    $data_field_name_rel = 'cf_wikiterms_rel';

    // Read in existing option value from database
  
    
    $opt_val_style = get_option($opt_name_style);
    $opt_val_rel = get_option($opt_name_rel);

    if( $_POST[ $hidden_field_name ] == 'Y' ) {
       
      
        $opt_val_style = $_POST[$data_field_name_style];
        $opt_val_rel = $_POST[$data_field_name_rel];
        
  
        
        update_option($opt_name_style, $opt_val_style);
        update_option($opt_name_rel, $opt_val_rel);		
        
?>
	<div id="message" class="updated fade"><p><strong><?php _e('Options saved.', 'wiki-style-autolinks' ); ?></strong></p></div>

<?php
    }
    echo '<div class="wrap">';
    echo "<h2>" . __( 'Wiki-style Autolinks Plugin Options', 'wiki-style-autolinks' ) . "</h2>";
    ?>

	<form name="form_Wikiterm_Options" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
	<p style="padding-left:25px">	
	<table>
	<tr style="vertical-align: middle; height: 40px">
		<td><?php _e("Localization code:", 'wiki-style-autolinks' ); ?></td>
		
	</tr>
	
	</tr>
	<tr style="vertical-align: middle; height: 40px"">
		<td><?php _e("Enable default CSS style:", 'wiki-style-autolinks' ); ?></td>
		<td>
			<select name="<?php echo $data_field_name_style; ?>">
				<option value="0" <?php if($opt_val_style == "0") echo 'selected' ?> ><?php _e("No", 'wiki-style-autolinks' ); ?></option> 
				<option value="1" <?php if($opt_val_style == "1") echo 'selected' ?> ><?php _e("Yes", 'wiki-style-autolinks' ); ?></option> 
			</select>
		</td>
	</tr>
	<tr style="vertical-align: middle; height: 40px"">
		<td><?php _e("Enable NOFOLLOW in hyperlink:", 'wiki-style-autolinks' ); ?></td>
		<td>
			<select name="<?php echo $data_field_name_rel; ?>">
				<option value="0" <?php if($opt_val_rel == "0") echo 'selected' ?> ><?php _e("No", 'wiki-style-autolinks' ); ?></option> 
				<option value="1" <?php if($opt_val_rel == "1") echo 'selected' ?> ><?php _e("Yes", 'wiki-style-autolinks' ); ?></option> 
			</select>
		</td>
	</tr>
	</table>
	</p>
	<p class="submit">
	<input type="submit" name="Submit" value="<?php _e('Update Options', 'wiki-style-autolinks' ) ?>" />
	</p>
	</form>
	</div>

<?php
}

/* Core function of the filter */

function cf_wikiterms($body = '')
{
	if (!strpos($body,"[A:")) return $body;

	/* Style definitions */
	$cf_wikiterm = "padding-bottom: 2px; border-bottom: 1px dotted #DD0000";
	$cf_wikiicon = "font-family: Georgia, Times New Roman, Serif; font-weight: bold; color: #AAAAAA";

	/* Apply direct style ? */
	if (intval(get_option('cf_wikiterms_style')) == 1) 
	{
		$css_class_term = "style=\"".$cf_wikiterm."\"";
		
	}
	else {
			$css_class_term = "class=\"wikiterm\"";
			
		 }
	
	/* loading parameters */
	
	
  	$relnofollow = ((intval(get_option('cf_wikiterms_rel')) == 1) ? " rel=\"nofollow\"" : '');
  
	/* if exists, set the wikiicon switch */ 
	if (stripos($body,"[wikiicon]") !== false)
	{
		$wikiIcon = 1;
		$body = str_ireplace("[wikiicon]", "", $body);
	}
	
	if (preg_match_all("@\[A:(.*?)\]@", $body, $Matches) > 0)
	{
		foreach ($Matches[1] as $pos => $Match)
		{
			$wikiTerm = trim($Match);			        
			$wikiDesc = __("From your site article about:",'wiki-style-autolinks').' '.$wikiTerm;				
			$wikiTermNS = str_replace(" ",  "_", $wikiTerm);
			
			if ($wikiIcon == 0) 
				$link = "<a href=\"http://www.yourdomain-url-here/".$wikiTermNS."\"".$relnofollow." target=\"_blank\" title=\"".$wikiDesc."\" ".$css_class_term." >".$wikiTerm."</a><sup ".$css_class_icon." ><em></em></sup>";
		    else
				$link = "<span ".$css_class_term." >".$wikiTerm."</span><sup><a href=\"http://www.yourdomain-url-here/".$wikiTermNS."\"".$relnofollow." target=\"_blank\" title=\"".$wikiDesc."\" ".$css_class_icon." ><em></em></a></sup>";
			
			if (strpos($Matches[0][$pos], ":0]") == (strlen($Matches[0][$pos]) - 3))
				$body = str_replace($Matches[0][$pos], str_replace(":0]", "]", $Matches[0][$pos]), $body); 
			else
				$body = str_replace($Matches[0][$pos], $link, $body);
		}
	}
	return $body;	
}

?>
