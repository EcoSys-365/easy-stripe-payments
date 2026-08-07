<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Loading Animation -->
<div id="espad-loading-overlay">
  <div class="loader"></div>
</div>

<?php require ESPAD_PLUGIN_PATH . 'admin/sections/preview/form-db.php'; ?>
  
<?php
$is_multistep_preview =
    strtolower( (string) $selected_form_mode ) === 'multistep';

$preview_wrapper_id = $is_multistep_preview
    ? 'espad_multistep_preview_page'
    : 'espad_page';
?>

<div
    id="<?php echo esc_attr( $preview_wrapper_id ); ?>"
    class="<?php
        echo esc_attr(
            'preview_page prev-mode-' .
            $mode .
            (
                $is_multistep_preview
                    ? ' espad-multistep-admin-preview'
                    : ''
            )
        );
    ?>"
>    
    
    <select
        name="preview_form_id"
        id="preview_form_id"
        class="form-select form-select-sm"
    >
        <option value="">
            <?php
            echo esc_html__(
                'Please choose a form',
                'easy-stripe-payments'
            );
            ?>
        </option>

        <?php if ( ! empty( $all_form_titles ) ) : ?>

            <?php foreach ( $all_form_titles as $form ) : ?>

                <option
                    value="<?php echo esc_attr( $form->id ); ?>"
                    <?php selected( $selected_form_id, $form->id ); ?>
                > 
                    <?php 
                    printf(
                        '%1$s (%2$s) - ID: %3$s',
                        esc_html( $form->form_name ),
                        esc_html( $form->mode ),
                        esc_html( $form->id )
                    );                    
                    ?>
                </option>

            <?php endforeach; ?>

        <?php else : ?>

            <option value="">
                <?php
                echo esc_html__(
                    'No forms found',
                    'easy-stripe-payments'
                );
                ?>
            </option>

        <?php endif; ?>
    </select>
    
    <?php if ( $selected_form_id != '' ) : ?>

        <div class="espad-preview-overlay">

            <div class="espad-preview-overlay__title">
                
                <?php echo esc_html__( 'Use this Shortcode', 'easy-stripe-payments' ); ?>
                
                <button type="button" class="espad-toggle">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>                
                
            </div>

            <div class="espad-preview-overlay__content">
                <p>
                    <?php echo esc_html__( 'Copy and paste this into a page or post.', 'easy-stripe-payments' ); ?>
                </p>
 
                <code id="shortcode-payment-form"><?php
                    if ( $selected_form_mode === 'Multistep' ) {
                        printf(
                            '[espad_multistep_checkout id="%d"]',
                            absint( $selected_form_id )
                        );
                    } else {
                        printf(
                            '[espad_payment_form id="%d"]',
                            absint( $selected_form_id )
                        );
                    }
                ?></code>                
                 
                <span class="espad-copy-feedback">
                    <?php echo esc_html__( 'Shortcode copied!', 'easy-stripe-payments' ); ?>
                </span>                
                
            </div>

        </div>

    <?php endif; ?> 
    
    <?php if ( strtolower( (string) $selected_form_mode ) !== 'multistep' ) : ?>
     
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
    
    <?php endif; ?>
     
    <?php if (
        $selected_form_id &&
        $selected_form_mode === 'Multistep'
    ) : ?>

        <div class="espad-multistep-preview">
            <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo espad_render_multistep_checkout(
                array(
                    'id' => $selected_form_id,
                )
            );
            ?>
        </div>

    <?php else : ?>

        <?php
        require ESPAD_PLUGIN_PATH .
            'admin/sections/preview/main-form.php';
        ?>

    <?php endif; ?>  
 
</div>  
    
