<?php

include_once(DN_LIBRARY_PATH . '/DN_Base.php');

class DN_Settings_Model extends DN_Base
{
  public function DN_Settings_Model()
  {
    parent::DN_Base();
  }
  
  public function saving_new_mode($post_data)
  {
    
    global $wpdb;
    $this->app()->load->library('DN_Upload');
    $this->app()->load->library('DN_Validation');
    
    if($_FILES['image']['size'] != 0)
    {
      /* upload mode */
      $uploader_args = array(
        'field' => 'image',
        'upload_path' => _BM._DEF._ORI.'/'
      );

      $this->app()->dn_upload->initialize($uploader_args);
      
      $new_file = $this->app()->dn_upload->upload();
      
      if(empty($new_file))
      {
        $this->app()->dn_validation->set_error('settings', 'general', __('Process not identified.'.$this->app()->dn_upload->errors, 'dn_bm'));
      }
      else
      {

        $this->check_dir_create($post_data['image_status'], $new_file['filename']);
        
        /* save to database */
        $wpdb->insert($wpdb->prefix.'banner_manager', array(
          'ref_data' => 'default',
          'description' => $post_data['description'],
          'link' => $post_data['link'],
          'img_src' => $new_file['filename'],
          'crop_cords' => $post_data['crop_cords'],
          'type' => 1,
          'active' => 1
        )); 
        
        $this->app()->dn_validation->set_success('settings', 'general', __('Saved.', 'dn_bm'));
      }
    }
    else
    {
      $this->app()->dn_validation->set_error('settings', 'general', __('Image File Cannot Be Empty', 'dn_bm'));
    }
    
  }
  
  public function saving_edit_mode($post_data)
  {
    
    global $wpdb;
    $this->app()->load->library('DN_Upload');
    $this->app()->load->library('DN_Validation');
    /* edit mode */
    $re_upload = FALSE;
    if($_FILES['image']['size'] != 0)
    {
      @unlink( BM_CONTENT_UPLOADS_DIR._DEF._ORI.'/'.$post_data['file'] );
      @unlink( BM_CONTENT_UPLOADS_DIR._DEF._PRO.'/'.$post_data['file'] );
      @unlink( BM_CONTENT_UPLOADS_DIR._DEF._TMP.'/'.$post_data['file'] );
      @unlink( BM_CONTENT_UPLOADS_DIR._DEF._TMP.'/mini_'.$post_data['file'] );
      
      $uploader_args = array(
        'field' => 'image',
        'upload_path' => _BM._DEF._ORI.'/'
      );
      $this->app()->dn_upload->initialize($uploader_args);
      
      $new_file = $this->app()->dn_upload->upload();
      $re_upload = TRUE;
    }

    if($re_upload){
      $img_src = $new_file['filename'];
    }elseif(!$re_upload){
      $img_src = $post_data['file'];
    }
      
    $this->check_dir_create($post_data['image_status'], $img_src);
      
    /* save to database */
    $wpdb->update($wpdb->prefix.'banner_manager', array(
      'description' => $post_data['description'],
      'link' => $post_data['link'],
      'img_src' => $img_src,
      'crop_cords' => $post_data['crop_cords'],
      'type' => 1,
      'active' => 1
    ), array('ref_data' => 'default'));
    
    $this->app()->dn_validation->set_success('settings', 'general', __('Saved.', 'dn_bm'));
  }
  
  public function delete_default_data()
  {
    global $wpdb;
    $this->app()->load->library('DN_Validation');
    
    $img_src = $wpdb->get_row('SELECT img_src FROM '.$wpdb->prefix.'banner_manager WHERE ref_data = "default"');
    $img_src = $img_src->img_src;

    if(!empty($img_src))
    {
      @unlink(BM_CONTENT_UPLOADS_DIR._DEF._ORI.'/'.$img_src);
      @unlink(BM_CONTENT_UPLOADS_DIR._DEF._TMP.'/'.$img_src);
      @unlink(BM_CONTENT_UPLOADS_DIR._DEF._TMP.'/mini_'.$img_src);
      @unlink(BM_CONTENT_UPLOADS_DIR._DEF._PRO.'/'.$img_src);
    }
    
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'banner_manager WHERE ref_data = "default"');
    $this->app()->dn_validation->set_success('settings', 'general', __('Deleted.', 'dn_bm'));
  }
  
  private function check_dir_create($image_status, $img_src)
  {
    if($image_status == 1)
    {
      if(!is_dir(BM_CONTENT_UPLOADS_DIR._DEF._PRO.'/'))
      {
        mkdir(BM_CONTENT_UPLOADS_DIR._DEF._PRO.'/', 0777, TRUE);
      }
      @copy(BM_CONTENT_UPLOADS_DIR._DEF._TMP.'/'.$img_src, BM_CONTENT_UPLOADS_DIR._DEF._PRO.'/'.$img_src);
      $image_status = 2;
    }
  }
  
  public function default_data()
  {
    global $wpdb;
    
    return $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."banner_manager WHERE type = 1");
  }
  
}

?>
