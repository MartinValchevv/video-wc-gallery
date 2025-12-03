jQuery(document).ready(function($) {

    /**
     * @since 2.0 Function show modal pricing info
     */
    const selectorTrigger = [
        '.open-vwg-modal-pro-info',                               // URL info PRO feature
        '.get-vwg-pro-version-info-btn',                          // Get pro button in metabox
        '#vwg_settings_custom_svg_icon',                          // Settings Use custom SVG
        '#vwg_settings_optimized_thumbnails',                     // Settings optimized thumbnails switch button
        '#vwg_settings_convert_on_upload',                        // Settings convert on upload
        '#vwg_bulk_convert',                                      // Button bulk convert
        '#vwg_delete_converted_files',                            // Button delete converted file
        '#vwg_metabox_thumbnails_optimization_settings #submit',  // Settings optimization settings submit button
    ]
    var selectors = selectorTrigger.join(', ')


    $(document).on('click', selectors, function(e) {
        e.preventDefault();
        Swal.fire({
            title: `<img src="${vwg_variable_obj.VWG_Url}/includes/images/vwg-logo.png" class="logo-image" title="Video Gallery for WooCommerce" alt="Video Gallery for WooCommerce">`,
            icon: false,
            width: 1000,
            showCloseButton: true,
            showCancelButton: false,
            focusConfirm: false,
            showConfirmButton: false,
            closeButtonHtml: '<i class="fas fa-times"></i>',
            customClass: {
                container: 'vwg-swal-container-pro-info',
                popup: 'vwg-swal-popup-pro-info',
                header: 'vwg-swal-header-pro-info',
                title: 'vwg-swal-title-pro-info',
                closeButton: 'vwg-swal-close-button'
            },
            willOpen: () => {
                $('#wpadminbar').css('z-index', '999') // WP admin bar under modal
                $('#adminmenuwrap').css('z-index', '990') // WP admin menu under modal
            },
            didClose: () => {
                $('#wpadminbar').css('z-index', '');  // WP admin bar reset z-index
                $('#adminmenuwrap').css('z-index', '');  // WP admin menu reset z-index
            },
            didOpen: () => {
                // Fix any button styling issues after modal opens
                $('.vwg-pricing-container .buy-now-btn').css({
                    'text-decoration': 'none',
                    'color': function() {
                        return $(this).closest('.recommended').length ? '#7e3fec' : 'white';
                    }
                });
            },
            html: `
               <div class="pricing-header">
                  <h1>Choose Your Plan</h1>
                  <p>Select the perfect plan for your business needs</p>
               </div>
               <div class="vwg-pricing-container">
                   <ul class="pricing-list">
                       <li>
                            <div class="pricing-card">
                                <h2>Single Site</h2>
                                <div class="price">
                                    <span class="currency">$</span>
                                    <span class="value">49</span>
                                    <span class="duration">one-time</span>
                                </div>
                                <ul class="pricing-features">
                                    <li><i class="fas fa-check"></i>1 WordPress site</li>
                                    <li class="premium-features"><i class="fas fa-check"></i>All premium features <i class="fas fa-info-circle tooltip-icon"></i></li>
                                    <li><i class="fas fa-check"></i>Free updates</li>
                                    <li><i class="fas fa-check"></i>Premium Support</li>
                                </ul>
                                <div class="pricing-footer">
                                    <a class="buy-now-btn" href="https://nitramix.com/projects/video-gallery-for-woocommerce#pricing" target="_blank">Buy Now</a>
                                </div>
                            </div>
                       </li>
                       <li class="recommended">
                            <div class="pricing-card">
                                <div class="recommended-badge">RECOMMENDED</div>
                                <h2>Multiple Sites</h2>
                                <div class="price">
                                    <span class="currency">$</span>
                                    <span class="value">89</span>
                                    <span class="duration">one-time</span>
                                </div>
                                <ul class="pricing-features">
                                    <li><i class="fas fa-check"></i>Up to 3 WordPress sites</li>
                                    <li class="premium-features"><i class="fas fa-check"></i>All premium features <i class="fas fa-info-circle tooltip-icon"></i></li>
                                    <li><i class="fas fa-check"></i>Free updates</li>
                                    <li><i class="fas fa-check"></i>Premium Support</li>
                                </ul>
                                <div class="pricing-footer">
                                    <a class="buy-now-btn" href="https://nitramix.com/projects/video-gallery-for-woocommerce#pricing" target="_blank">Buy Now</a>
                                </div>
                            </div>
                       </li>
                       <li>
                            <div class="pricing-card">
                                <h2>Unlimited Sites</h2>
                                <div class="price">
                                    <span class="currency">$</span>
                                    <span class="value">189</span>
                                    <span class="duration">one-time</span>
                                </div>
                                <ul class="pricing-features">
                                    <li><i class="fas fa-check"></i>Unlimited WordPress sites</li>
                                    <li class="premium-features"><i class="fas fa-check"></i>All premium features <i class="fas fa-info-circle tooltip-icon"></i></li>
                                    <li><i class="fas fa-check"></i>Free updates</li>
                                    <li><i class="fas fa-check"></i>Premium Support</li>
                                </ul>
                                <div class="pricing-footer">
                                    <a class="buy-now-btn" href="https://nitramix.com/projects/video-gallery-for-woocommerce#pricing" target="_blank">Buy Now</a>
                                </div>
                            </div>
                       </li>
                   </ul>
                   <div class="money-back-guarantee">
                       <div class="guarantee-icon">
                           <i class="fas fa-undo-alt"></i>
                       </div>
                       <h3>14-Day Money Back Guarantee</h3>
                       <p>If you're not satisfied, we offer a full refund within 14 days. No questions asked.</p>
                   </div>
               </div>
                `, // END HTML Pricing table
        });

    });


    /**
     * @since 2.0 Function switch pricing plan with animations
     */
    // Hide the subtle gradient layer (.pricing-list > li::after) when pricing table has been scrolled to the end (mobile version only)
    $(document).on('scroll', '.pricing-body', function() {
        var selected = $(this);
        window.requestAnimationFrame(function(){checkScrolling(selected)});
    });

    function checkScrolling(tables){
        tables.each(function(){
            var table= $(this),
                totalTableWidth = parseInt(table.children('.pricing-features').width()),
                tableViewport = parseInt(table.width());
            if( table.scrollLeft() >= totalTableWidth - tableViewport -1 ) {
                table.parent('li').addClass('is-ended');
            } else {
                table.parent('li').removeClass('is-ended');
            }
        });
    }

});