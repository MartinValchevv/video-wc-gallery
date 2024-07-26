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
            showCloseButton: false,
            showCancelButton: false,
            focusConfirm: false,
            showConfirmButton: false,
            customClass: {
                container: 'vwg-swal-container-pro-info',
                popup: 'vwg-swal-popup-pro-info',
                header: 'vwg-swal-header-pro-info',
                title: 'vwg-swal-title-pro-info',
            },
            willOpen: () => {
                $('#wpadminbar').css('z-index', '999') // WP admin bar under modal
                $('#adminmenuwrap').css('z-index', '990') // WP admin menu under modal
            },
            didClose: () => {
                $('#wpadminbar').css('z-index', '');  // WP admin bar reset z-index
                $('#adminmenuwrap').css('z-index', '');  // WP admin menu reset z-index
            },
            html: `
               <p>You can check <a href="#" target="_blank">here</a> for more information on unlocking the Pro version and its features.</p>
               <div class="vwg-pricing-container">
                   <div class="pricing-switcher">
                        <p class="fieldset">
                            <input type="radio" name="duration-1" value="monthly" id="monthly-1" checked>
                            <label for="monthly-1">Monthly</label>
                            <input type="radio" name="duration-1" value="yearly" id="yearly-1">
                            <label for="yearly-1">Yearly</label>
                            <span class="switch"></span>
                        </p>
                   </div>
                   <ul class="pricing-list bounce-invert">
                       <li>
                            <ul class="pricing-wrapper">
                                <li data-type="monthly" class="is-visible">
                                    <header class="pricing-header">
                                            <h2>Basic</h2>
                                            <div class="price">
                                                <span class="currency">$</span>
                                                <span class="value">12</span>
                                                <span class="duration">month</span>
                                            </div>
                                    </header>
                                    <div class="pricing-body">
                                        <ul class="pricing-features">
                                            <li><i class="fas fa-check"></i><em>1</em> Site License</li>
                                            <li><i class="fas fa-check"></i><em>1</em> Template Style</li>
                                            <li><i class="fas fa-check"></i><em>25</em> Products Loaded</li>
                                            <li><i class="fas fa-check"></i><em>1</em> Image per Product</li>
                                            <li><i class="fas fa-check"></i><em>Unlimited</em> Bandwidth</li>
                                            <li><i class="fas fa-check"></i><em>24/7</em> Support</li>
                                        </ul>
                                    </div>
                                    <footer class="pricing-footer">
                                        <a class="select" href="#">Get PRO</a>
                                    </footer>
                                </li>
                                <li data-type="yearly" class="is-hidden">
                                    <header class="pricing-header">
                                        <h2>Basic</h2>
                                        <div class="price">
                                            <span class="currency">$</span>
                                            <span class="value">100</span>
                                            <span class="duration">year</span>
                                        </div>
                                    </header>
                                    <div class="pricing-body">
                                        <ul class="pricing-features">
                                            <li><i class="fas fa-check"></i><em>1</em> Site License</li>
                                            <li><i class="fas fa-check"></i><em>1</em> Template Style</li>
                                            <li><i class="fas fa-check"></i><em>25</em> Products Loaded</li>
                                            <li><i class="fas fa-check"></i><em>1</em> Image per Product</li>
                                            <li><i class="fas fa-check"></i><em>Unlimited</em> Bandwidth</li>
                                            <li><i class="fas fa-check"></i><em>24/7</em> Support</li>
                                        </ul>
                                    </div>
                                    <footer class="pricing-footer">
                                        <a class="select" href="#">Get PRO</a>
                                    </footer>
                                </li>
                            </ul>
                       </li>
                       <li class="exclusive">
                          <ul class="pricing-wrapper">
                             <li data-type="monthly" class="is-visible">
                                <header class="pricing-header">
                                   <h2>Exclusive</h2>
                                   <div class="price">
                                        <span class="currency">$</span>
                                    <span class="value">32</span>
                                    <span class="duration">month</span>
                                   </div>
                                </header>
                                <div class="pricing-body">
                                    <ul class="pricing-features">
                                        <li><i class="fas fa-check"></i><em>5</em> Site License</li>
                                        <li><i class="fas fa-check"></i><em>3</em> Template Styles</li>
                                        <li><i class="fas fa-check"></i><em>40</em> Products Loaded</li>
                                        <li><i class="fas fa-check"></i><em>7</em> Images per Product</li>
                                        <li><i class="fas fa-check"></i><em>Unlimited</em> Bandwidth</li>
                                        <li><i class="fas fa-check"></i><em>24/7</em> Support</li>
                                    </ul>
                                </div>
                                <footer class="pricing-footer">
                                   <a class="select" href="#">Get PRO</a>
                                </footer>
                             </li>
                             <li data-type="yearly" class="is-hidden">
                                <header class="pricing-header">
                                    <h2>Exclusive</h2>
                                    <div class="price">
                                        <span class="currency">$</span>
                                        <span class="value">290</span>
                                        <span class="duration">year</span>
                                    </div>
                                </header>
                                <div class="pricing-body">
                                    <ul class="pricing-features">
                                        <li><i class="fas fa-check"></i><em>5</em> Site License</li>
                                        <li><i class="fas fa-check"></i><em>3</em> Template Styles</li>
                                        <li><i class="fas fa-check"></i><em>40</em> Products Loaded</li>
                                        <li><i class="fas fa-check"></i><em>7</em> Images per Product</li>
                                        <li><i class="fas fa-check"></i><em>Unlimited</em> Bandwidth</li>
                                        <li><i class="fas fa-check"></i><em>24/7</em> Support</li>
                                    </ul>
                                </div>
                                <footer class="pricing-footer">
                                    <a class="select" href="#">Get PRO</a>
                                </footer>
                             </li>
                          </ul>
                       </li>
                        <li>
                            <ul class="pricing-wrapper">
                                <li data-type="monthly" class="is-visible">
                                    <header class="pricing-header">
                                        <h2>Pro</h2>
                                        <div class="price">
                                            <span class="currency">$</span>
                                            <span class="value">72</span>
                                            <span class="duration">month</span>
                                        </div>
                                    </header>
                                    <div class="pricing-body">
                                       <ul class="pricing-features">
                                           <li><i class="fas fa-check"></i><em>Unlimited</em> Site License</li>
                                            <li><i class="fas fa-check"></i><em>5</em> Template Styles</li>
                                            <li><i class="fas fa-check"></i><em>50</em> Products Loaded</li>
                                            <li><i class="fas fa-check"></i><em>10</em> Images per Product</li>
                                            <li><i class="fas fa-check"></i><em>Unlimited</em> Bandwidth</li>
                                            <li><i class="fas fa-check"></i><em>24/7</em> Support</li>
                                       </ul>
                                    </div>
                                    <footer class="pricing-footer">
                                       <a class="select" href="#">Get PRO</a>
                                    </footer>
                                </li>
                                <li data-type="yearly" class="is-hidden">
                                    <header class="pricing-header">
                                        <h2>Pro</h2>
                                        <div class="price">
                                            <span class="currency">$</span>
                                            <span class="value">590</span>
                                            <span class="duration">year</span>
                                        </div>
                                    </header>
                                    <div class="pricing-body">
                                        <ul class="pricing-features">
                                            <li><i class="fas fa-check"></i><em>Unlimited</em> Site License</li>
                                            <li><i class="fas fa-check"></i><em>5</em> Template Styles</li>
                                            <li><i class="fas fa-check"></i><em>50</em> Products Loaded</li>
                                            <li><i class="fas fa-check"></i><em>10</em> Images per Product</li>
                                            <li><i class="fas fa-check"></i><em>Unlimited</em> Bandwidth</li>
                                            <li><i class="fas fa-check"></i><em>24/7</em> Support</li>
                                        </ul>
                                    </div>
                                    <footer class="pricing-footer">
                                        <a class="select" href="#">Get PRO</a>
                                    </footer>
                                </li>
                            </ul>
                        </li>
                   </ul>
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

    $(document).on('change', '.vwg-pricing-container input[type="radio"]', function(event) {
        event.preventDefault();
        var selected_filter = $(this).val();
        var pricing_table = $(this).closest('.vwg-pricing-container');
        var filter_list_container = pricing_table.children('.pricing-switcher');
        var pricing_table_wrapper = pricing_table.find('.pricing-wrapper');
        var table_elements = {};
        filter_list_container.find('input[type="radio"]').each(function(){
            var filter_type = $(this).val();
            table_elements[filter_type] = pricing_table_wrapper.find('li[data-type="'+filter_type+'"]');
        });
        show_selected_items(table_elements[selected_filter]);
        pricing_table_wrapper.addClass('is-switched').eq(0).one('webkitAnimationEnd oanimationend msAnimationEnd animationend', function() {
            hide_not_selected_items(table_elements, selected_filter);
            pricing_table_wrapper.removeClass('is-switched');
            if (pricing_table.find('.pricing-list').hasClass('bounce-invert')) pricing_table_wrapper.toggleClass('reverse-animation');
        });
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

    function show_selected_items(selected_elements) {
        selected_elements.addClass('is-selected');
    }

    function hide_not_selected_items(table_containers, filter) {
        $.each(table_containers, function(key, value){
            if ( key != filter ) {
                $(this).removeClass('is-visible is-selected').addClass('is-hidden');
            } else {
                $(this).addClass('is-visible').removeClass('is-hidden is-selected');
            }
        });
    }

});