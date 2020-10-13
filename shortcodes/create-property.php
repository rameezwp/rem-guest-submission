<div class="ich-settings-main-wrap">
<section id="new-property">
	<form id="create-property-gust-user"  data-ajaxurl="<?php echo admin_url( 'admin-ajax.php' ); ?>">
		<input type="hidden" name="action" value="rem_gust_create_pro_ajax">
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
						<p style="text-align: center">
							<!-- <div style="height:0px;overflow:hidden"> -->
								<input  type="file" id="imageupload" name="images[]" multiple="multiple" accept="image/*"  />
							<!-- </div> -->
							<!-- <button type="button" class="btn btn-default guest_upload_image_button" data-title="<?php _e( 'Select images for property gallery', 'real-estate-manager' ); ?>" data-btntext="<?php _e( 'Insert', 'real-estate-manager' ); ?>">
								<span class="dashicons dashicons-images-alt2"></span>
								<?php _e( 'Click here to Upload Images', 'real-estate-manager' ); ?>
							</button> -->
						</p>
						<p>
							<?php echo nl2br(rem_get_option('upload_images_inst')); ?>
						</p>
						<div class="thumbs-prev">

						</div>
						<div style="clear: both; display: block;"></div>						
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
							if (in_array($name, $valid_tabs) && $name!= 'property_attachments') { ?>
							<div class="info-block" id="<?php echo $name; ?>">
								<div class="section-title line-style">
									<h3 class="title"><?php echo $title; ?></h3>
								</div>

								<div class="row property-meta-fields <?php echo $name; ?>-fields">
									<?php
									global $rem_sc_ob;
										foreach ($inputFields as $field) {
											if($field['tab'] == $name && $field['accessibility'] != 'disable'){
												$rem_sc_ob->render_property_field($field);
											}
										}
									?>
								</div>
							</div>
						<?php } 
						}
					?>
					
					<div class="info-block" id="attachemnts">
						<div class="section-title line-style">
							<h3 class="title"><?php _e( 'Attachemnts', 'real-estate-manager' ); ?></h3>
						</div>
						<p style="text-align: center">
							<input  type="file" id="attachemnts" name="attachemnts[]" multiple="multiple" accept="application/pdf,application/vnd.ms-excel"  />
						</p>
						<div style="clear: both; display: block;"></div>						
					</div>
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
						<div class="col-sm-6 col-xs-12 space-form wrap-name">
								<label for="property_user_name" class="label-p-title">
									<?php _e("Name", 'real-estate-manager') ?>
								</label>
							<input id="property_user_name" class="form-control" value="" type="text" name="property_user_name" required>
						</div>
						<div class="col-sm-6 col-xs-12 space-form wrap-phone">
								<label for="property_user_phone" class="label-p-title">
									<?php _e("Phone No.", 'real-estate-manager') ?>
								</label>
							<input id="property_user_phone" class="form-control" value="" type="tel" name="property_user_phone" required>
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