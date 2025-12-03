jQuery(document).ready(function($) {

    /**
     * @since 1.0 Add admin setting page Color Picker
     */
    $('.vwg_settings_icon_color').wpColorPicker({
        defaultColor: '#ffffff' // Set the default color to white
    });

    /**
     * @since 2.0 Initializes WooCommerce help tips using TipTip.
     */
    $('.woocommerce-help-tip').tipTip({attribute: 'data-tip', fadeIn: 50, fadeOut: 50});

    /**
     * @since 1.13 Function if mute is off automatically turns off autoplay
     */
    $('#vwg_settings_muted').on('change', function (){
        $('#vwg_autoplay_settings_info').remove()
        if (!this.checked) {
            if ($('#vwg_settings_autoplay').prop('checked')) {
                $('#vwg_settings_autoplay').prop('checked', false)
                $('#vwg_settings_autoplay').parent('td').append(`<div id="vwg_autoplay_settings_info" class="notice notice-info alt"><p>${translate_obj.autoplay_settings_info}</p></div>`)
            }
        }
    })

    /**
     * @since 2.0 Add confirm change this settings remove_settings_data
     */
    $('#vwg_settings_remove_settings_data').on('change', function (){
        if (this.checked) {
            Swal.fire({
                title: translate_obj.are_you_sure,
                text: translate_obj.remove_plugin_data_txt,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: translate_obj.yes,
                cancelButtonText: translate_obj.cancel_text,
            }).then((result) => {
                if (!result.isConfirmed) {
                    $(this).prop('checked', false);
                }
            })
        }
    })

    /**
     * @since 2.0 Add confirm change this settings remove_videos_data
     */
    $('#vwg_settings_remove_videos_data').on('change', function (){
        if (this.checked) {
            Swal.fire({
                title: translate_obj.are_you_sure,
                text: translate_obj.remove_video_txt,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: translate_obj.yes,
                cancelButtonText: translate_obj.cancel_text,
            }).then((result) => {
                if (!result.isConfirmed) {
                    $(this).prop('checked', false);
                }
            })
        }
    })

    /**
     * @since 2.0 Function for delete unused thumbs
     */
    $('#delete_unused_thumbs').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: translate_obj.are_you_sure,
            text: translate_obj.to_delete_unused_thumbs,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: translate_obj.yes,
            cancelButtonText: translate_obj.cancel_text,
        }).then((result) => {
            if (result.isConfirmed) {
                // User clicked "OK"
                Swal.fire({
                    title: translate_obj.deleting,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                });
                Swal.showLoading();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'remove_unused_thumbnails',
                        security: vwg_AJAX.security,
                        files_for_del: $('#files_for_delete').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: response.data.count_delete + ' ' + translate_obj.deleting_thumbs,
                                html: `<textarea readonly style="width: 100%; min-height: 150px;">${response.data.deleted_file.join('\n')}</textarea>`,
                                icon: 'success',
                                confirmButtonColor: '#7e3fec',
                            });
                            $('.vwg-dashboard-widgets-unused-thumbs').remove();
                        } else {
                            // Handle the AJAX error
                            console.log('AJAX Error: ' + response.data);
                            Swal.fire({
                                title: translate_obj.error,
                                text: translate_obj.ajaxError + response.data,
                                icon: 'error',
                                confirmButtonColor: '#7e3fec',
                            });
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.log('AJAX Error: ' + errorThrown);
                        Swal.fire({
                            title: translate_obj.error,
                            text: translate_obj.ajaxError + errorThrown,
                            icon: 'error',
                            confirmButtonColor: '#7e3fec',
                        });
                    }
                });
            }
        });
    });

});