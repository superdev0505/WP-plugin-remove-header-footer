<?php 
$post_id = $post->ID;
wp_nonce_field($this->textname, 'custom_nonce');
$this->render_section_fields($post_id, 'normal');
?>
<br/>

<?php do_action('vg_page_layout/metabox/after_content', $post); ?>
<hr/>
<p><?php _e('If the plugin doesn´t work as expected you can use the <a href="#" class="vg-page-layout-advanced">advanced settings</a>.', $this->textname); ?></p>
<div class="vg-page-layout-advanced-content" style="display: none;">
	<?php $this->render_section_fields($post_id, 'advanced'); ?>	
	<?php do_action('vg_page_layout/metabox/after_advanced_content', $post); ?>
</div>
<script>
	jQuery(document).ready(function () {
		jQuery('.vg-page-layout-advanced').click(function (e) {
			e.preventDefault();
			jQuery('.vg-page-layout-advanced-content').slideToggle();
		});
	});
</script>