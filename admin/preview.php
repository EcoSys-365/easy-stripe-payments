<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Loading Animation -->
<div id="espad-loading-overlay">
  <div class="loader"></div>
</div>

<?php require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/form-db.php'; ?>
 
<div id="espad_page" class="preview_page prev-mode-<?php echo esc_html($mode); ?>"> 
    
    <select name="preview_form_id" id="preview_form_id" class="form-select form-select-sm">
        <option value=""><?php echo esc_html(__( 'Please choose a form', 'easy-stripe-payments' )); ?></option>
        <?php if ( ! empty( $all_form_titles ) ) : ?>
            <?php foreach ( $all_form_titles as $form ) : ?>
                <option value="<?php echo esc_attr( $form->id ); ?>" <?php echo $selected_form_id == $form->id ? 'selected' : ''; ?>>    
                    <?php echo esc_html( $form->form_name ); ?>
                </option>
            <?php endforeach; ?>
        <?php else : ?>
            <option value=""><?php echo esc_html(__( 'No forms found', 'easy-stripe-payments' )); ?></option>
        <?php endif; ?>
    </select>
    
    <h2 class="desktop"><?php echo esc_html(__( 'Preview Desktop', 'easy-stripe-payments' )); ?>   
        
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#333" viewBox="0 0 24 24">
          <path d="M3 3h18a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm0 2v11h18V5H3zm7 14h4v2h-4v-2z"/>
        </svg>
        
    </h2>
    
    <h2 class="tablet hidden"><?php echo esc_html(__( 'Preview Tablet', 'easy-stripe-payments' )); ?>   
        
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path d="M19 0H5C3.9 0 3 .9 3 2v20c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V2c0-1.1-.9-2-2-2zm0 22H5V2h14v20zm-7-1c.55 0 1-.45 1-1s-.45-1-1-1-1 .45-1 1 .45 1 1 1z"/>
        </svg>
        
    </h2>    
    
    <h2 class="mobile hidden"><?php echo esc_html(__( 'Preview Mobile', 'easy-stripe-payments' )); ?>   
        
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path d="M17 0H7C5.89 0 5 .89 5 2v20c0 1.1.89 2 2 2h10c1.1 0 2-.9 2-2V2c0-1.1-.9-2-2-2zm-5 21H8v-1h4v1zm3-3H6V4h12v14z"/>
        </svg>
        
    </h2> 
     
    <select class="size-changer form-select form-select-sm">
        <option value="desktop" selected><?php echo esc_html(__( 'Desktop', 'easy-stripe-payments' )); ?></option>
        <option value="tablet"><?php echo esc_html(__( 'Tablet', 'easy-stripe-payments' )); ?></option>
        <option value="mobile"><?php echo esc_html(__( 'Mobile', 'easy-stripe-payments' )); ?></option>
    </select> 
   
    <?php require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/main-form.php'; ?>

</div>  
    
