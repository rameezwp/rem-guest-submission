<?php 
	$form_fields = get_option( 'rem_property_fields'.$form_id );
 ?>
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
					<?php if ($add_images) { ?>
						<div class="info-block" id="images">
							<div class="section-title line-style">
								<h3 class="title"><?php _e( 'Images', 'real-estate-manager' ); ?></h3>
							</div>
							<?php if (is_user_logged_in()) {  ?>
								<p style="text-align: center">
									<button type="button" class="btn btn-default upload_image_button" data-title="<?php _e( 'Select images for property gallery', 'real-estate-manager' ); ?>" data-btntext="<?php _e( 'Insert', 'real-estate-manager' ); ?>">
										<span class="dashicons dashicons-images-alt2"></span>
										<?php _e( 'Click here to Upload Images', 'real-estate-manager' ); ?>
									</button>
								</p>
								<p>
									<?php echo nl2br(rem_get_option('upload_images_inst')); ?>
								</p>
								<div class="thumbs-prev">

								</div>
							<?php } else {  ?>
								<p style="text-align: center">
									<input  type="file" id="imageupload" name="images[]" multiple="multiple" accept="image/*"  />
								</p>
								<div class="thumbs-prev">

								</div> <?php 
							} ?>	
							<div style="clear: both; display: block;"></div>						
						</div>
					
						<?php
					}
					if ($add_attachments) { ?>
					<div class="info-block" id="attachemnts">
						<div class="section-title line-style">
							<h3 class="title"><?php _e( 'Attachemnts', 'real-estate-manager' ); ?></h3>
						</div>
						<p style="text-align: center">
							<input  type="file" id="attachemnts" name="attachemnts[]" multiple="multiple" accept="application/pdf,application/vnd.ms-excel"  />
						</p>
						<div style="clear: both; display: block;"></div>						
					</div>
						<?php
					} 
					if (!empty($form_fields)) { ?>
						<div class="info-block" id="general_settings">
							<div class="section-title line-style">
								<h3 class="title">Fields</h3>
							</div>

							<div class="row property-meta-fields">
								<?php
								global $rem_sc_ob;
									foreach ($form_fields as $field) {
										if($field['accessibility'] != 'disable'){
											$rem_sc_ob->render_property_field($field);
										}
									}
								?>
							</div>
						</div>
						
						<?php
					}?>
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