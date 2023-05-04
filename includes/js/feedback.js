jQuery(document).ready(function ($) {
    var src = $('#the-list').find('[data-slug="video-wc-gallery"] span.deactivate a').attr('href')

    $('#the-list').find('[data-slug="video-wc-gallery"] span.deactivate a').attr('href', 'javascript:;')

    $('#the-list').find('[data-slug="video-wc-gallery"] span.deactivate a').on('click', function (e) {
        e.preventDefault();
        $('#vwg-popup-container').addClass('show');
    });

    $('.vwg-skip').on('click', function (e) {
        e.preventDefault();
        $('#vwg-popup-container').removeClass('show');
        Swal.fire({
            title: translate_obj.deactivating,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
        });
        Swal.showLoading();
        location.href = src;
    });

    // Close popup when clicking outside of it
    $(document).on('click', function(event) {
        if ($(event.target).hasClass('show')) {
            $('#vwg-popup-container').removeClass('show');
        }
    });

    // Show/hide text field when selecting/deselecting 'Other' option
    $('#vwg-feedback-form input[type="radio"][name="reason"]').on('change', function() {
        $('#vwg_other-reason').addClass('hidden');
        $('#vwg_which-plugin').addClass('hidden');
        $('#vwg_other-reason-text').prop('required',false);

        if ($(this).val() === 'other') {
            $('#vwg_other-reason').removeClass('hidden');
            $('#vwg_other-reason-text').prop('required',true);
        } else if ($(this).val() === 'alternative') {
            $('#vwg_which-plugin').removeClass('hidden');
            $('#vwg_other-reason-text').prop('required',false);
            $('#vwg_other-reason').addClass('hidden');
        }
    });

    // Submit feedback form
    $('#vwg-feedback-form').on('submit', function(e) {
        e.preventDefault();
        $('#vwg-popup-container').removeClass('show');
        Swal.fire({
            title: translate_obj.deactivating,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
        });
        Swal.showLoading();


        const formData = $(this).serializeArray();
        var serialized_data = {};

        $.each(formData, function(index, obj){
            serialized_data[obj.name] = obj.value;
        });

        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'vwg_send_deactivation_feedback_email',
                form_data: serialized_data,
            },
            success: function(response) {
                if (response.success) {
                    // $('#vwg-popup-container').removeClass('show');
                    location.href = src;
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error submitting feedback: ' + errorThrown);
                Swal.close();
            }
        });
    });
});