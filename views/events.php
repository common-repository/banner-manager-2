<!-- start content-outer START -->
<div class="wrap">

  <div id="icon-edit" class="icon32"></div>
  <h2>
    Banner Manager - Page Url
    <a class="button add-new-h2" href="<?php echo get_admin_url().'admin.php?page=dn_bm-events&act=edit'; ?>">Add New</a>
  </h2>

  <?php if(!empty($errors['general'])): ?>
    <div class="error below-h2" id="fi_message-error">
      <p>
        <?php  echo $errors['general']; ?>
        <a href="javascript:void(0);" class="fi_close-error">close</a>
      </p>
    </div>
  <?php endif; ?>
  
  <?php if(!empty($success['general'])): ?>
    <div class="updated below-h2" id="fi_message-succes">
      <p>
        <?php  echo $success['general']; ?>
        <a href="javascript:void(0);" class="fi_close-succes">close</a>
      </p>
    </div>
  <?php endif; ?>
  
  <div class="nav_tab">
    <a href="admin.php?page=dn_bm">Settings</a> | 
    <a href="admin.php?page=dn_bm-page_url">Page Url</a> | 
    <a href="admin.php?page=dn_bm-referral_url">Referral Url</a> | 
    Events
  </div>
  
  <div class="bm_wrap">
    <div class="calendar_container fleft">
      <?php echo $calendar; ?>
    </div>
    <form name="insert_event_form" id="insert_event_form" method="POST" action="" enctype="multipart/form-data">
      <table class="widefat">
        <thead>
          <tr>
            <th scope="col" width="10">#ID</th>
            <th scope="col" width="80">&nbsp;</th>
            <th scope="col">Events Date</th>
            <th scope="col" width="200">Description</th>
            <th scope="col">File</th>
            <th scope="col">Link</th>
            <th scope="col" width="100">Actions</th>
          </tr>
        </thead>
        <?php if(count($events_list) > 0): foreach($events_list as $pul): ?>
        <tbody>
          <tr class="<?php $i++; echo (($i%2) == 1)?'':'alternate'; ?>">
            <td scope="col"><?php echo $pul->id; ?></td>
            <td scope="col"><img src="<?php echo BM_CONTENT_UPLOADS_URL._EVE._TMP.'/mini_'.$pul->img_src; ?>?ver=<?php echo date('mydhis'); ?>" /></td>
            <td scope="col"><strong><a href="<?php echo $pul->ref_data; ?>" target="_blank"><?php echo $pul->ref_data; ?></a></strong></td>
            <td scope="col"><?php echo $pul->description; ?></td>
            <td scope="col"><?php echo $pul->img_src; ?></td>
            <td scope="col"><strong><a href="<?php echo $pul->link; ?>" target="_blank"><?php echo $pul->link; ?></a></strong></td>
            <td scope="col">
              <a class="dn_icon-1" href="<?php echo WP_ADMIN_URL.'admin.php?page=dn_bm-events&act=edit&ref_data='.$pul->ref_data; ?>" title="edit">&nbsp;</a>
              <a class="dn_icon-2" href="<?php echo wp_nonce_url(WP_ADMIN_URL.'admin.php?page=dn_bm-events&act=del&bm_id='.$pul->id, 'dn-bm-delete-events'); ?>" title="delete">&nbsp;</a>
              <a class="<?php echo ($pul->active == 1)?'dn_icon-3':'dn_icon-5'; ?>" href="<?php echo wp_nonce_url(WP_ADMIN_URL.'admin.php?page=dn_bm-events&act=act&action='.$pul->active.'&bm_id='.$pul->id, 'dn-bm-active-events'); ?>" title="active/inactive">&nbsp;</a> 
            </td>
          </tr>
        </tbody>
        <?php endforeach; endif; ?>
      </table>
      <?php echo $pagination; ?>
    </form>
  </div>
</div>
<!-- end content-outer START -->
