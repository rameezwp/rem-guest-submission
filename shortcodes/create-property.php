<div class="ich-settings-main-wrap">
<section id="new-property">
	<form id="create-property-guest-user"  data-ajaxurl="<?php echo admin_url( 'admin-ajax.php' ); ?>">
		<input type="hidden" name="action" value="rem_guest_create_pro_ajax">
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<div class="info-block" id="basic">
						<div class="section-title line-style no-margin">
							<h3 class="title"><?php _e( 'Basic Information', 'real-estate-manager' ); ?></h3>
						</div>
						<div class="row">
							<div class="col-md-12 space-form">
								<input id="title" class="form-control" type="text" required placeholder="<?php _e( 'Property Title', 'real-estate-manager' ); ?>" name="title">
							</div>
							<div class="col-md-12">
								<?php wp_editor( __( 'Property Description', 'real-estate-manager' ), 'rem-content', array(
									'quicktags' => array( 'buttons' => 'strong,em,del,ul,ol,li,close' ),
									'textarea_name' => 'content',
									'editor_height' => 350
								) ); ?>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>

					<div class="info-block" id="images">
						<div class="section-title line-style">
							<h3 class="title"><?php _e( 'Images', 'real-estate-manager' ); ?></h3>
						</div>
						<p style="text-align: center" id="image-upload-btn-area">
						<?php 
						$images_limit = rem_get_option('gallery_images_limit', 5);
						for ($i=0; $i < $images_limit; $i++) { ?>
							
							<label for="guest_upload_image_<?php echo  $i; ?>" class="btn btn-default">
								<span class="dashicons dashicons-images-alt2"></span>
								<?php _e( 'Click to Upload Image ', 'real-estate-manager' ); ?>
								<input id="guest_upload_image_<?php echo  $i; ?>" type="file" class="imageupload" name="property_images[]" accept="image/*"  />
							</label>
						<?php } ?>
						</p>
						<div class="thumbs-prev">

						</div>
						<div style="clear: both; display: block;"></div>						
						<p>
							<?php echo nl2br(rem_get_option('upload_images_inst')); ?>
						</p>
					</div>
	
					<?php
						global $rem_ob;
						$inputFields = $rem_ob->get_all_property_fields();
						$tabsData = rem_get_single_property_settings_tabs();
				        $valid_tabs = array();
				        foreach ($tabsData as $tab_key => $tab_title) {
				            foreach ($inputFields as $field) {
				                $field_tab = (isset($field['tab'])) ? $field['tab'] : '' ;
				                if ($tab_key == $field_tab && !in_array($field_tab, $valid_tabs)) {
				                   $valid_tabs[] = $field_tab; 
				                }
				            }
				        }
						foreach ($tabsData as $name => $title) {
							if ( $name == 'property_attachments_jh') { ?>
								<div class="info-block" id="<?php echo $name; ?>">
								<div class="section-title line-style">
									<h3 class="title"><?php echo $title; ?></h3>
								</div>

								<div class="row property-meta-fields attachments <?php echo $name; ?>-fields" id="attachemnts">
								<p style="text-align: center" id="attachments-upload-btn-area">
									<label for="guest_upload_attachments" class="btn btn-default">
										<span class="dashicons dashicons-images-alt2"></span>
										<?php _e( 'Click here to Upload Attachments', 'real-estate-manager' ); ?>
										<input id="guest_upload_attachments" type="file" class="attachmentsupload" id="attachemnts" name="attachemnts[]" />
									</label>
								</p>
								<div class="attachments-prev">

								</div>
							</div>
							<?php }else if(in_array($name, $valid_tabs)) { ?>
							<div class="info-block" id="<?php echo $name; ?>">
								<div class="section-title line-style">
									<h3 class="title"><?php echo $title; ?></h3>
								</div>

								<div class="row property-meta-fields <?php echo $name; ?>-fields">
									<?php
										foreach ($inputFields as $field) {
											if($field['tab'] == $name && $field['accessibility'] != 'disable'){
												$this->render_property_field($field);
											}
										}
									?>
								</div>
							</div>
						<?php } 
						}
					?>
					
					<div class="info-block" id="tags">
						<div class="section-title line-style">
							<h3 class="title"><?php _e( 'Tags', 'real-estate-manager' ); ?></h3>
						</div>
						<div class="row features-box">
							<div class="col-lg-12">
								<p><?php _e( 'Each tag separated by comma', 'real-estate-manager' ); ?>  <code>,</code></p>
								<textarea class="form-control" name="tags"></textarea>
							</div>
						</div>
					</div>
					
					<div class="info-block" id="map">
						<div class="section-title line-style">
							<h3 class="title"><?php _e( 'Place on Map', 'real-estate-manager' ); ?></h3>
						</div>
						<?php if (rem_get_option('use_map_from', 'leaflet') == 'google_maps') { ?>
							<input type="text" class="form-control" id="search-map" placeholder="<?php _e( 'Type to Search...', 'real-estate-manager' ); ?>">
						<?php } ?>
						
						<div id="map-canvas" style="height: 300px"></div>

						<div id="position"><i class="fa fa-map-marker-alt"></i> <?php _e( 'Drag the pin to the location on the map', 'real-estate-manager' ); ?></div>
					</div>
					<div class="section-title line-style">
						<h3 class="title"><?php _e("User Info", 'real-estate-manager') ?></h3>
					</div>
					<div class="row">
						<div class="col-sm-12 col-xs-12 space-form wrap-name">
							<label for="property_user_name" class="label-p-title">
								<?php _e("Email", 'real-estate-manager') ?>
							</label>
							<input id="rem_user_email" class="form-control"  type="email" name="rem_user_email" required>
						</div>
					</div>
					<?php do_action( 'rem_create_property_before_submit' ); ?>
					
					<input class="btn btn-default" id="form-submit" type="submit" value="<?php _e( 'Create Property', 'real-estate-manager' ); ?>">
					<?php do_action( 'rem_create_property_after_submit' ); ?>
					<br>
					<br>
					<div class="alert with-icon alert-info creating-prop" style="display:none;" role="alert">
						<i class="icon fa fa-info"></i>
						<span class="msg"><?php _e( 'Please wait...', 'real-estate-manager' ); ?></span>
					</div>
				</div>
			</div>
	</form>
</section>
</div>
<style>
#images input,
.upload-attachments-wrap input {
	display: none!important;
}
.attachments-prev div,
.thumbs-prev div {
	position: relative;
	width: 60px;
	height: 60px;
    /*overflow: hidden;*/
	float: left;
    padding: 5px;
    margin-bottom: 0;
    margin-top: 10px;
    margin-right: 15px;
    border: 1px solid #e3e3e3;
    background: #f7f7f7;
    -moz-border-radius: 3px;
    -khtml-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;	
	cursor: all-scroll;
}
.thumbs-prev div img,
.thumbs-prev div span.file-type-icon {
    width: 100%;
}
.thumbs-prev div .rem-media-delete,
.attachments-prev div .rem-media-delete {
	position: absolute;
	top: -8px;
	right: -8px;
	color: red;
	cursor: pointer;
}
.ui-accordion .ui-accordion-header {
  cursor: pointer;
  position: relative;
  margin-top: 1px;
  zoom: 1;
}
#accordion .ui-accordion-content {
    max-width: 100%;
    background-color: #ffffff;
    color: #777;
    padding: 10px;
}
#accordion .ui-accordion-content p {
    margin: 0;
}
#accordion .ui-accordion-header {
    background-color: #ccc;
    margin: 0px;
	padding: 10px 20px;
	color: #ffffff;
	outline: none;
}
#accordion .ui-accordion-header a {
    color: #fff;
    display: block;
    width: 100%;
    text-indent: 10px;
}
#accordion .ui-accordion-header {
    background-color: #389abe;
    background-image: -moz-linear-gradient(top,  #389abe 0%, #2a7b99 100%); /* FF3.6+ */
    background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#389abe), color-stop(100%,#2a7b99)); /* Chrome,Safari4+ */
    background-image: -webkit-linear-gradient(top,  #389abe 0%,#2a7b99 100%); /* Chrome10+,Safari5.1+ */
    background-image: -o-linear-gradient(top,  #389abe 0%,#2a7b99 100%); /* Opera 11.10+ */
    background-image: -ms-linear-gradient(top,  #389abe 0%,#2a7b99 100%); /* IE10+ */
    background-image: linear-gradient(to bottom,  #389abe 0%,#2a7b99 100%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#389abe', endColorstr='#2a7b99',GradientType=0 ); /* IE6-9 */
}
#accordion .ui-accordion-header a {
    text-shadow: 1px 1px 0px rgba(0,0,0,0.2);
    text-shadow: 1px 1px 0px rgba(0,0,0,0.2);
    border-right: 1px solid rgba(0, 0, 0, .2);
    border-left: 1px solid rgba(0, 0, 0, .2);
    border-bottom: 1px solid rgba(0, 0, 0, .2);
    border-top: 1px solid rgba(250, 250, 250, .2);
}
.file-type-icon {
  display: inline-block;
  margin: 0 auto;
  position: relative;
  color: black;
  height: 55px;
}
.file-type-icon::before {
  position: absolute;
  width: 48px;
  height: 60px;
  left: 0;
  top: 0px;
  content: '';
  border: solid 2px #920035;
}
.file-type-icon::after {
  content: 'file';
  content: attr(filetype);
  left: -4px;
  padding: 0px 2px;
  text-align: right;
  line-height: 1.3;
  position: absolute;
  background-color: #000;
  color: #fff;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 1px;
  top: 9px;
}
.file-type-icon .fileCorner {
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 11px 0 0 11px;
  border-color: white transparent transparent #920035;
  position: absolute;
  top: 0px;
  left: 38px;
}
.rem-attachment-icon a {
  margin-left: 45px;
}
.rem-attachment-icon {
  height: 45px;
}

/* Icons Colors */
.file-type-icon.zip::before {
  border: solid 2px #6082AF;
}
.file-type-icon.zip::after {
  background-color: #6082AF;
}
.file-type-icon.zip .fileCorner {
  border-color: white transparent transparent #6082AF;
}
.file-type-icon.mp3::before {
  border: solid 2px #1584DD;
}
.file-type-icon.mp3::after {
  background-color: #1584DD;
}
.file-type-icon.mp3 .fileCorner {
  border-color: white transparent transparent #1584DD;
}
.file-type-icon.jpg::before {
  border: solid 2px #208895;
}
.file-type-icon.jpg::after {
  background-color: #208895;
}
.file-type-icon.jpg .fileCorner {
  border-color: white transparent transparent #208895;
}
.file-type-icon.pdf::before {
  border: solid 2px #AA0000;
}
.file-type-icon.pdf::after {
  background-color: #AA0000;
}
.file-type-icon.pdf .fileCorner {
  border-color: white transparent transparent #AA0000;
}
.file-type-icon.ppt::before {
  border: solid 2px #D14424;
}
.file-type-icon.ppt::after {
  background-color: #D14424;
}
.file-type-icon.ppt .fileCorner {
  border-color: white transparent transparent #D14424;
}
</style>