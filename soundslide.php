<?php
/**
 * Plugin Name: Soundslides
 * Description: This plugin adds the support for Soundslides.
 * Author: WPoets Team
 * Plugin URI: 
 * Author URI: http://www.wpoets.com
 * Version: 1.4.2
 * =======================================================================
 */
global $sslide_uploaded_filename;
function sslide_add_to_editor($html, $id, $attachment){
    
    if(  get_post_mime_type( $id ) == 'application/Soundslides' ){

      $url = $attachment['url'];
    
      $url_path =  explode('/', $url);
      unset($url_path[count($url_path)-1]);
      $url_path= implode('/', $url_path );

      $html = $url_path.'/'.$id.'/'.'soundslide.xml' ;            
      
      $found = true ;
      $filename='';

      
      if($found)
      { 
        $xml = @simplexml_load_file( $html );
        //print_r( $sml );
          $width = '0';
          $height ='0';
          if( $xml ){
              foreach($xml->meta->item as $item)
              {
                  if($item->parameter == 'index_width')
                    $width = $item->value;
                  
                  if($item->parameter == 'index_height')
                    $height = $item->value;

  		            if($item->parameter == 'custom_width')
                    $width = $item->value;

                  if($item->parameter == 'custom_height')
                    $height = $item->value;
              }

              $html='[soundslides width="'.$width.'" height ="'.$height.'" id="'.$id.'"]';
        }   else {
              $html='HTTP Service Unavailable';
        }
      }
    }
    return $html;
}
add_filter('media_send_to_editor','sslide_add_to_editor',10,3);

function sslide_shortcode_handler($attr){
   $attr =shortcode_atts( array(
   'width' => 800,
   'height' => 596,
   'id' => 0,      
   ), $attr );
   
   if($attr['id']) {
       $fp= wp_get_attachment_url( $attr['id']); 
       
       $dirpath = dirname(get_attached_file($attr['id']));
       
      // echo $files .'url ' .plugins_url( '' , __FILE__ ).'js';
       $fp = explode('/',$fp);
       unset($fp[count($fp)-1]);
       $fp=implode('/',$fp);
      
       if(file_exists($dirpath.'/'.$attr['id'].'/_files')){
           $files=$fp.'/'.$attr['id'].'/_files';
       
       }
       else{
           $files =plugins_url( '' , __FILE__ ).'/js';
       }
       $ss_project = $fp.'/'.$attr['id'];
       
       wp_enqueue_script('jquery-ui-core',false,array(''),'1.0',true);
       wp_enqueue_script('jquery-ui-slider',false,array(''),'1.0',true);
       wp_enqueue_script('swfobject',false,array(''),'1.0',true);
       wp_enqueue_script('jplayer', $files.'/jquery.jplayer.min.js', array('jquery'), '1.0', true);
       wp_enqueue_script('soundslides', $files.'/soundslides.js', array('jquery'), '1.0', true);
       wp_enqueue_style('soundslide-css',$files.'/soundslides.css');
  /*  */
    
   echo '<div id="slideshow">
			<div id="object"></div>
		</div>';
   echo '';
  ?>
    <style>
        #slideshow{
            width:<?php echo $attr['width'] ?>px;
            height:<?php echo $attr['height'] ?>px;
        }
    </style>
    <script>
    jQuery(document).ready(function(){
        
       if (swfobject.getFlashPlayerVersion().major <= 9 ) {
					
					// Flash not available, use JS player
					var _slideshow = SOUNDSLIDES.player;
						//alert(_slideshow);			
					_slideshow.config = {
						'container_div': 'object',
						'path': '<?php echo  $ss_project; ?>/',
						'path_to_jplayer_swf' : '<?php echo  $files; ?>/',
						'width': <?php echo $attr['width'] ?>,
						'height': <?php echo $attr['height'] ?>
					};
					
					_slideshow.init();

				} else {

					// Flash available
					var flashvars = {
						pathToProject: "<?php echo  $ss_project; ?>/",
						format: "xml",
						resize_mode: "AUTOSIZE"
					};
					var params = {
						menu: "false",
						bgcolor: '#FFFFFF',
						wmode: "transparent",
						allowfullscreen: "true",
						allowscriptaccess: "always"
					};
					var attributes = {
						id: "myDynamicContent",
						name: "myDynamicContent"
					};

					swfobject.embedSWF("<?php echo  $ss_project; ?>/soundslider.swf", "object", <?php echo $attr['width'] ?>, <?php echo $attr['height'] ?>, "9.0.0",false, flashvars, params, attributes);
				}
               });
         </script>   
        <?php
        
   }
}

add_shortcode( 'soundslides', 'sslide_shortcode_handler' );


function sslide_init(){
    
    wp_enqueue_script('jquery');
}

add_action('init','sslide_init');


function sslide_handle_upload($arr){
    // test if uploaded file is soundslide
   global $sslide_uploaded_filename;
   global $sslide_soundslide_filename;
    $sslide_uploaded_filename='';
    require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
    $found=false;
    $zip = new PclZip($arr['file']);
    $zip->listContent();
    if (($lists = $zip->listContent()) == 0) {

    }
    else
    {
        foreach($lists as $file)
        {
            if(strpos($file['filename'], 'soundslide.xml'))
            {
                $found=true;
                $list = $zip->extract(PCLZIP_OPT_BY_NAME,  $file['stored_filename'], PCLZIP_OPT_EXTRACT_AS_STRING );
                 $sslide_soundslide_filename = $file['stored_filename'];
                if ($list != 0) 
                {
                    $xml= simplexml_load_string($list[0]['content']) ; 

                    //width and height
                    foreach($xml->meta->item as $item)
                    {
                        if($item->parameter == 'header_headline')
                        { 
                            $sslide_uploaded_filename  = $item->value;
                            break;
                        }   
                    }
                }        

            }
        }
    }
    
    if($found)
    {
        add_action('add_attachment', sslide_attachments);
    }
        
    return $arr;
}

add_filter('wp_handle_upload', 'sslide_handle_upload' );

function sslide_attachments($aid){
    global $wpdb;
    global $sslide_uploaded_filename;
    global $sslide_soundslide_filename;
    // i have id and i know it is soundslide
    //so update it it's mime type
    $data=array('post_mime_type'=>'application/Soundslides');
    
    if(strlen($sslide_uploaded_filename))
        $data['post_title']=$sslide_uploaded_filename;
    
    $wpdb->update( $wpdb->posts, $data, array( 'ID' => $aid ) );
    
    //now extract it
    
    //we need to extract the files now, as it will be used from now on.
    require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
    
    
    $file= get_attached_file($aid, true);
    $zip = new PclZip($file);
    $base_folder = explode('/',$sslide_soundslide_filename);
    $zip->extract(PCLZIP_OPT_PATH,dirname($file).'/'.$aid,PCLZIP_OPT_REMOVE_PATH, $base_folder[0]);    
    
}
function sslide_change_mime_icon($icon, $mime, $post_id ){
    
    if($mime=='application/Soundslides')
    {
            $icon =plugins_url('',__FILE__).'/icons/sslide.png';
            add_filter('icon_dir','sslide_icon_dir');
    }
    
    return $icon;
}

add_filter('wp_mime_type_icon', 'sslide_change_mime_icon',110,3);


function sslide_icon_dir($basepath){
    
    return dirname(__FILE__).'/icons';
}


function sslide_get_attached_file($file, $attachmentid){
   // echo 'file: '.$file.' '.$attachmentid;
   $mime = get_post_mime_type($attachmentid);
    if($mime=='application/Soundslides')
    {
        $file=str_replace('.zip','.soundslides',$file);
        return $file;
    }
    
    return $file;
}
add_filter('get_attached_file','sslide_get_attached_file',10,2);

function sslide_change_view_link($actions, $post, $detached){
    
    if($post->post_mime_type=='application/Soundslides')
    {
        //$uploads = wp_upload_dir();
        $url=wp_get_attachment_url($post->ID); // $uploads['baseurl'].'/'. $post->ID.'/index.html'
        
         
        $url_path =  explode('/', $url);
        unset($url_path[count($url_path)-1]);
        $url_path= implode('/', $url_path);
        
        $actions['view'] = '<a href="' .$url_path.'/'.$post->ID. '/index.html" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post->post_name ) ) . '" rel="permalink" target="_blank">' . __( 'View' ) . '</a>';
    }    
    return $actions;
}
add_filter('media_row_actions','sslide_change_view_link',10,3);


// 
add_action('admin_menu', 'admin_set_menu' );
function admin_set_menu(){
	add_options_page( 'Soundslide Settings', 'Soundslide Settings', 'manage_options', 'soundslide-settings', 'options_page' );
}



// function to get press posts yealrly 
add_action( 'wp_ajax_attach_zip_to_media', 'attach_zip_to_media' );
add_action( 'wp_ajax_nopriv_attach_zip_to_media', 'attach_zip_to_media' );
function attach_zip_to_media(){
	 $path_parts = pathinfo( $_POST['filename'] );
	/*echo $path_parts['dirname'], "\n";
	echo $path_parts['basename'], "\n";
	echo $path_parts['extension'], "\n";*/
	//echo $path_parts['filename'], "\n"; // since PHP 5.2.0
	$upload_dir = wp_upload_dir();
	
	$filename = $_POST['filename'] ;
	$attachment = array(
     //'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $filename ),
      'guid' => $upload_dir['basedir'].$upload_dir['subdir'].'/'.$filename,
     'post_mime_type' => 'application/Soundslides',
     'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
     'post_content' => '',
     'post_status' => 'inherit'
  	);
  	$aid = wp_insert_attachment( $attachment, $filename );
  
	//echo $aid = wp_insert_post( $post );
	$arr = array(
		'file' => $upload_dir['basedir'].'/soundslide/'.$_POST['filename'],
		'url' => $upload_dir['basedir'].'/soundslide/'.$_POST['filename'],
		'type' => 'application/zip' 
	);
	// sslide_handle_upload( $s_files );
	
	global $sslide_uploaded_filename;
   	global $sslide_soundslide_filename;
    $sslide_uploaded_filename='';
    require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
    $found=false;
    $zip = new PclZip($arr['file']);

    if (($lists = $zip->listContent()) == 0) {
		echo  'Invalid Format';
    }
    else
    {
        foreach($lists as $file)
        {
            if(strpos($file['filename'], 'soundslide.xml'))
            {
                $found=true;
                $list = $zip->extract(PCLZIP_OPT_BY_NAME,  $file['stored_filename'], PCLZIP_OPT_EXTRACT_AS_STRING );
                $sslide_soundslide_filename = $file['stored_filename'];
                if ($list != 0) 
                {
                    $xml= simplexml_load_string($list[0]['content']) ; 

                    //width and height
                    foreach($xml->meta->item as $item)
                    {
                        if($item->parameter == 'header_headline')
                        { 
                            $sslide_uploaded_filename  = $item->value;
                            break;
                        }   
                    }
                }        

            }
        }
        
    }
	//require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
    //$file= get_attached_file( $aid, true);
     if( $found )
     {
	    $zip = new PclZip( $arr['file'] );
	    $base_folder = explode('/',$sslide_soundslide_filename);
	    $zip->extract( PCLZIP_OPT_PATH, $upload_dir['basedir']."/".$aid  , PCLZIP_OPT_REMOVE_PATH, $base_folder[0]);
     	if ( copy( $arr['file'] , $upload_dir['basedir'].$upload_dir['subdir'].'/'.$filename )) {
			unlink( $arr['file'] );
		}

     }
    echo 1;
	die();	
}


function options_page() {
	echo '<div class="wrap">';
		echo '<div id="ss_message" class=""> 	</div>';
		screen_icon('options-general');
		echo '<h2>Soundslide Settings</h2>';
		echo '<br/><br/><p style="width:80%;">
		If you are having problems uploading your project through the media browser you can try to import larger projects by uploading via an FTP client <a href="http://en.wikipedia.org/wiki/Comparison_of_FTP_client_software" target="_blank">(http://en.wikipedia.org/wiki/Comparison_of_FTP_client_software)</a>.<br/><br/> 
Upload your zip compressed "publish_to_web" folder via FTP to the following directory on your hosting server /wp-content/uploads/soundslide.<br/><br/> 
After the upload is complete, click the attach button to import the projects to the media library. Your soundslides projects will be available in the media library and you can then insert the project into your page or post.<p><br/><br/>
<table class="form-table" style="width:80%;">';
		echo '<tr>';
		echo '<td>';
		echo '<label >Files Uploaded :</label>';
		echo '</td><td>';

			$upload_dir = wp_upload_dir();
			$filelist = glob( $upload_dir['basedir'].'/soundslide/*.zip'); 

			foreach($filelist as $file) 
			{ 
				//echo basename ( $file );
			    echo '<input type="text" name="zipfilename" value="'.basename( $file ).'" id="zipfilename" class="regular-text code soundslides_zip" disabled="disabled">';
			    
			}  
		
		echo '</td></tr>';
		echo '<tr><td></td><td>';
		echo '<button type="button" id="attachziptomedia" class="button-primary">Attach</button>';
		echo '</td></tr>';
		echo '</table>';
	echo '</div>';
} 

add_action( 'admin_enqueue_scripts', 'enqueue_ss_script' );
function enqueue_ss_script( $page ) {
    wp_enqueue_script( 'ss-script', plugin_dir_url(__FILE__).'attach-ajax.js', null, null, true );
}
// delete extracted files on deleting soundslide from media
add_action( 'delete_attachment', 'delete_attachment_soundslide_extracted' );
function delete_attachment_soundslide_extracted( $aid ){
$mime = get_post_mime_type( $aid );
    if( $mime=='application/Soundslides' )
    {
        $upload_dir = wp_upload_dir();
        recursive_remove_directory( $upload_dir['basedir'].'/'.$aid );
        $fp = get_attached_file( $aid, true );
        unlink ( $fp );
    }	
}

//
function recursive_remove_directory($directory, $empty=FALSE)
{
	// if the path has a slash at the end we remove it here
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}

	// if the path is not valid or is not a directory ...
	if(!file_exists($directory) || !is_dir($directory))
	{
		// ... we return false and exit the function
		return FALSE;

	// ... if the path is not readable
	}elseif(!is_readable($directory))
	{
		// ... we return false and exit the function
		return FALSE;

	// ... else if the path is readable
	}else{

		// we open the directory
		$handle = opendir($directory);
		while (FALSE !== ($item = readdir($handle)))
		{
			if($item != '.' && $item != '..')
			{
				$path = $directory.'/'.$item;
				if(is_dir($path)) 
				{
					recursive_remove_directory($path);
				}else{
					unlink($path);
				}
			}
		}
		closedir($handle);
		if($empty == FALSE)
		{
			if(!rmdir($directory))
			{
				return FALSE;
			}
		}
		return TRUE;
	}
}