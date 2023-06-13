jQuery(document).ready(function($) {

    /**
     * @since 1.0 Add warning class for this settings
     */
    $('#vwg_settings_remove_settings_data').parent().prev().addClass('vwg-settings-warning')
    $('#vwg_settings_remove_videos_data').parent().prev().addClass('vwg-settings-warning')

    /**
     * @since 1.0 Add admin setting page Color Picker
     */
    $('.vwg_settings_icon_color').wpColorPicker({
        defaultColor: '#ffffff' // Set the default color to white
    });

    /**
     * @since 1.0 Add confirm change this settings remove_settings_data
     */
    $('#vwg_settings_remove_settings_data').on('change', function (){
        if (this.checked) {
            Swal.fire({
                title: translate_obj.are_you_sure,
                text: translate_obj.remove_plugin_data_txt,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
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
     * @since 1.0 Add confirm change this settings remove_videos_data
     */
    $('#vwg_settings_remove_videos_data').on('change', function (){
        if (this.checked) {
            Swal.fire({
                title: translate_obj.are_you_sure,
                text: translate_obj.remove_video_txt,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
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
     * @since 1.3 Function for delete unused thumbs
     */
    $('#delete_unused_thumbs').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: translate_obj.deleting,
            // onBeforeOpen: () => {
            //     Swal.showLoading();
            // },
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
        });
        Swal.showLoading()

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'remove_unused_thumbnails',
                files_for_del : $('#files_for_delete').val()
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: response.data.count_delete+' '+translate_obj.deleting_thumbs,
                        html: `<textarea readonly style="width: 100%; min-height: 150px;">${response.data.deleted_file.join('\n')}</textarea>`,
                        icon: 'success',
                    });
                    $('.vwg-dashboard-widgets-unused-thumbs').remove()
                } else {
                    // Handle the AJAX error
                    console.log('AJAX Error: ' + response.data);
                    // Swal.fire({
                    //     title: 'Error',
                    //     text: 'AJAX Error: ' + response.data,
                    //     icon: 'error',
                    // });
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                // Swal.fire({
                //     title: 'Error',
                //     text: 'AJAX Error: ' + errorThrown,
                //     icon: 'error',
                // });
                console.log('AJAX Error: ' + errorThrown);
            }
        });
    });


});