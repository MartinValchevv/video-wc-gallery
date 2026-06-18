<?php
/**
 * Thumbnail editor modal for the product video gallery.
 *
 * Extracted from functions/do.php to keep that file lean. Provides a popup where
 * the user can preview the current thumbnail, scrub the video to pick ANY frame
 * (no typing seconds), upload their own image, or reset to the first frame. Each
 * action writes a base64 PNG (URL fallback) into the existing hidden
 * video_thumb_url field, so the save pipeline regenerates the thumbnail and all
 * WooCommerce sizes exactly as for an auto-captured frame.
 *
 * @since 2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Output the shared thumbnail editor modal markup. Rendered once inside the
 * product video tab and populated per video when opened.
 *
 * @since 2.9
 */
function vwg_render_thumbnail_editor_modal() {
    ?>
    <!-- Thumbnail manager modal (shared, populated per video on open) -->
    <div id="vwg-thumb-modal" class="vwg-modal vwg-thumb-modal">
        <div class="vwg-modal-content">
            <div class="vwg-modal-header">
                <h2><i class="fas fa-image"></i> <?php echo esc_html__('Edit thumbnail', 'video-wc-gallery'); ?></h2>
                <span class="vwg-modal-close">&times;</span>
            </div>
            <div class="vwg-modal-body vwg-thumb-body">
                <div class="vwg-thumb-stage">
                    <video class="vwg-thumb-video" muted playsinline preload="auto" crossorigin="anonymous"></video>
                </div>
                <div class="vwg-thumb-controls">
                    <button type="button" class="vwg-thumb-playpause" title="<?php echo esc_attr__('Play / pause', 'video-wc-gallery'); ?>"><i class="fas fa-play"></i></button>
                    <input type="range" class="vwg-thumb-scrubber" min="0" max="0" step="0.1" value="0" />
                    <span class="vwg-thumb-time">0:00 / 0:00</span>
                </div>
                <p class="vwg-thumb-hint"><?php echo esc_html__('Drag the slider to the frame you like, then click “Use this frame”.', 'video-wc-gallery'); ?></p>

                <div class="vwg-thumb-current">
                    <span class="vwg-thumb-current-label"><?php echo esc_html__('Current thumbnail', 'video-wc-gallery'); ?></span>
                    <img class="vwg-thumb-current-img" src="" alt="" />
                </div>
            </div>
            <div class="vwg-modal-footer vwg-thumb-footer">
                <button type="button" class="button vwg-thumb-reset"><i class="fas fa-rotate-left"></i> <?php echo esc_html__('Reset to first frame', 'video-wc-gallery'); ?></button>
                <button type="button" class="button vwg-thumb-upload"><i class="fas fa-upload"></i> <?php echo esc_html__('Upload image', 'video-wc-gallery'); ?></button>
                <button type="button" class="button button-primary vwg-thumb-capture"><i class="fas fa-camera"></i> <?php echo esc_html__('Use this frame', 'video-wc-gallery'); ?></button>
                <button type="button" class="button vwg-thumb-done"><?php echo esc_html__('Done', 'video-wc-gallery'); ?></button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Print the thumbnail editor styles + behaviour in the product edit footer.
 *
 * @since 2.9
 */
function vwg_thumbnail_editor_assets() {
    ?>
    <style>
        /* Thumbnail manager modal (self-contained, works without PRO) */
        #vwg-thumb-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 100000;
            background: rgba(0,0,0,0.6);
            align-items: center;
            justify-content: center;
        }
        #vwg-thumb-modal .vwg-modal-content {
            background: #fff;
            width: 92%;
            max-width: 640px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: vwgThumbIn 0.18s ease;
        }
        @keyframes vwgThumbIn {
            from { transform: translateY(12px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        #vwg-thumb-modal .vwg-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
        }
        #vwg-thumb-modal .vwg-modal-header h2 {
            margin: 0;
            font-size: 17px;
            color: #2d3748;
        }
        #vwg-thumb-modal .vwg-modal-header h2 i { color: #6c5ce7; }
        #vwg-thumb-modal .vwg-modal-close {
            cursor: pointer;
            font-size: 26px;
            line-height: 1;
            color: #999;
            transition: color 0.2s ease;
        }
        #vwg-thumb-modal .vwg-modal-close:hover { color: #ff5252; }
        #vwg-thumb-modal .vwg-modal-body { padding: 20px; }
        #vwg-thumb-modal .vwg-thumb-stage {
            position: relative;
            width: 100%;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 16 / 9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #vwg-thumb-modal .vwg-thumb-video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
        }
        #vwg-thumb-modal .vwg-thumb-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
        }
        #vwg-thumb-modal .vwg-thumb-playpause {
            flex: 0 0 auto;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            background: #6c5ce7;
            color: #fff;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        #vwg-thumb-modal .vwg-thumb-playpause:hover { background: #5a4bd4; }
        #vwg-thumb-modal .vwg-thumb-scrubber {
            flex: 1 1 auto;
            -webkit-appearance: none;
            appearance: none;
            height: 6px;
            border-radius: 3px;
            background: #e2e2ec;
            cursor: pointer;
            outline: none;
        }
        #vwg-thumb-modal .vwg-thumb-scrubber::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #6c5ce7;
            border: 3px solid #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.3);
            cursor: pointer;
        }
        #vwg-thumb-modal .vwg-thumb-scrubber::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #6c5ce7;
            border: 3px solid #fff;
            cursor: pointer;
        }
        #vwg-thumb-modal .vwg-thumb-time {
            flex: 0 0 auto;
            font-size: 12px;
            color: #666;
            font-variant-numeric: tabular-nums;
            min-width: 84px;
            text-align: right;
        }
        #vwg-thumb-modal .vwg-thumb-hint {
            margin: 10px 2px 0;
            font-size: 12px;
            color: #888;
        }
        #vwg-thumb-modal .vwg-thumb-current {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #eee;
        }
        #vwg-thumb-modal .vwg-thumb-current-label {
            font-size: 13px;
            font-weight: 600;
            color: #555;
        }
        #vwg-thumb-modal .vwg-thumb-current-img {
            width: 96px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: #f0f0f0;
        }
        #vwg-thumb-modal .vwg-thumb-current-img[src=""] { visibility: hidden; }
        #vwg-thumb-modal .vwg-modal-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 16px 20px;
            border-top: 1px solid #eee;
            background: #f9f9fb;
        }
        #vwg-thumb-modal .vwg-modal-footer .vwg-thumb-reset { margin-right: auto; }
        #vwg-thumb-modal .vwg-modal-footer .button i { margin-right: 4px; }
    </style>

    <script type="text/javascript">
        jQuery(document).ready(function($) {

            var $thumbModal = $('#vwg-thumb-modal');
            if (!$thumbModal.length) {
                return;
            }
            var thumbModalVideo = $thumbModal.find('.vwg-thumb-video')[0];
            var thumb_uploader;
            var thumbScrubbing = false;

            function vwgFmtTime(s) {
                s = Math.floor(s || 0);
                var m = Math.floor(s / 60);
                var sec = s % 60;
                return m + ':' + (sec < 10 ? '0' : '') + sec;
            }

            function vwgUpdateThumbTime() {
                $thumbModal.find('.vwg-thumb-time').text(
                    vwgFmtTime(thumbModalVideo.currentTime) + ' / ' + vwgFmtTime(thumbModalVideo.duration)
                );
            }

            function vwgThumbToast(msg) {
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success', title: msg,
                    showConfirmButton: false, timer: 2500, timerProgressBar: true
                });
            }

            // Apply a thumbnail (dataURI for save + previewSrc to show) to the target card.
            function vwgApplyThumb(dataURI, previewSrc) {
                var videoId = $thumbModal.data('video-id');
                var videoItem = $('.video_id_' + videoId);
                var shown = previewSrc || dataURI;

                videoItem.find('.video_thumb_url').val(dataURI);
                videoItem.find('.video-player').css('background-image', "url('" + shown + "')");
                videoItem.find('video').attr('poster', shown);
                $thumbModal.find('.vwg-thumb-current-img').attr('src', shown);
            }

            // Capture the frame currently shown in the modal scrubber video.
            function vwgCaptureModalFrame() {
                if (!thumbModalVideo || !thumbModalVideo.videoWidth) {
                    return;
                }
                var canvas = document.createElement('canvas');
                canvas.width = thumbModalVideo.videoWidth;
                canvas.height = thumbModalVideo.videoHeight;
                canvas.getContext('2d').drawImage(thumbModalVideo, 0, 0, canvas.width, canvas.height);

                var dataURI;
                try {
                    dataURI = canvas.toDataURL('image/png');
                } catch (e) {
                    vwgThumbToast('<?php echo esc_js(__('Could not read this video frame (cross-origin). Try uploading an image instead.', 'video-wc-gallery')); ?>');
                    return;
                }
                vwgApplyThumb(dataURI, dataURI);
                vwgThumbToast('<?php echo esc_js(__('Frame set as thumbnail. Save the product to apply.', 'video-wc-gallery')); ?>');
            }

            // Open the modal for a video card.
            $(document).on('click', '.action-btn.change-thumb-btn', function() {
                var videoId = $(this).data('video-id');
                var videoItem = $('.video_id_' + videoId);
                var videoSrc = videoItem.find('.video_url').val();
                var currentThumb = videoItem.find('.video_thumb_url').val();

                // YouTube videos use their own thumbnail — the frame editor doesn't apply.
                var videoType = videoItem.find('.video_type').val();
                if (videoType === 'youtube' || /(youtube\.com|youtu\.be)/i.test(videoSrc || '')) {
                    return;
                }

                $thumbModal.data('video-id', videoId);
                $thumbModal.find('.vwg-thumb-current-img').attr('src', currentThumb || '');
                $thumbModal.find('.vwg-thumb-scrubber').val(0);
                $thumbModal.find('.vwg-thumb-time').text('0:00 / 0:00');
                $thumbModal.find('.vwg-thumb-playpause i').attr('class', 'fas fa-play');

                thumbModalVideo.pause();
                thumbModalVideo.src = videoSrc;
                thumbModalVideo.load();

                $thumbModal.css('display', 'flex');
            });

            // Build the scrubber once metadata (duration) is known.
            $(thumbModalVideo).on('loadedmetadata', function() {
                $thumbModal.find('.vwg-thumb-scrubber').attr('max', this.duration || 0).val(0);
                vwgUpdateThumbTime();
            });

            // Drag the scrubber → seek the video so its frame previews live.
            $thumbModal.on('mousedown touchstart', '.vwg-thumb-scrubber', function() { thumbScrubbing = true; });
            $(document).on('mouseup touchend', function() { thumbScrubbing = false; });
            $thumbModal.on('input change', '.vwg-thumb-scrubber', function() {
                if (thumbModalVideo.duration) {
                    thumbModalVideo.currentTime = parseFloat($(this).val());
                }
            });
            $(thumbModalVideo).on('timeupdate seeked', function() {
                if (!thumbScrubbing) {
                    $thumbModal.find('.vwg-thumb-scrubber').val(thumbModalVideo.currentTime);
                }
                vwgUpdateThumbTime();
            });

            // Play / pause toggle.
            $thumbModal.on('click', '.vwg-thumb-playpause', function() {
                if (thumbModalVideo.paused) { thumbModalVideo.play(); }
                else { thumbModalVideo.pause(); }
            });
            $(thumbModalVideo).on('play', function() { $thumbModal.find('.vwg-thumb-playpause i').attr('class', 'fas fa-pause'); });
            $(thumbModalVideo).on('pause', function() { $thumbModal.find('.vwg-thumb-playpause i').attr('class', 'fas fa-play'); });

            // Capture the current frame as the thumbnail.
            $thumbModal.on('click', '.vwg-thumb-capture', function() {
                vwgCaptureModalFrame();
            });

            // Reset to the first frame of the video.
            $thumbModal.on('click', '.vwg-thumb-reset', function() {
                if (!thumbModalVideo.videoWidth) { return; }
                $(thumbModalVideo).one('seeked', function() {
                    vwgCaptureModalFrame();
                });
                thumbModalVideo.currentTime = 0;
            });

            // Upload your own image as the thumbnail.
            $thumbModal.on('click', '.vwg-thumb-upload', function() {
                if (!thumb_uploader) {
                    thumb_uploader = wp.media({
                        title: '<?php echo esc_js(__('Select thumbnail image', 'video-wc-gallery')); ?>',
                        button: { text: '<?php echo esc_js(__('Use as thumbnail', 'video-wc-gallery')); ?>' },
                        library: { type: 'image' },
                        multiple: false
                    });
                    thumb_uploader.on('select', function() {
                        var image = thumb_uploader.state().get('selection').first().toJSON();
                        var img = new Image();
                        img.onload = function() {
                            var canvas = document.createElement('canvas');
                            canvas.width = img.naturalWidth;
                            canvas.height = img.naturalHeight;
                            canvas.getContext('2d').drawImage(img, 0, 0);
                            var dataURI;
                            try { dataURI = canvas.toDataURL('image/png'); }
                            catch (e) { dataURI = image.url; }
                            vwgApplyThumb(dataURI, image.url);
                            vwgThumbToast('<?php echo esc_js(__('Thumbnail updated. Save the product to apply.', 'video-wc-gallery')); ?>');
                        };
                        img.onerror = function() {
                            vwgApplyThumb(image.url, image.url);
                            vwgThumbToast('<?php echo esc_js(__('Thumbnail updated. Save the product to apply.', 'video-wc-gallery')); ?>');
                        };
                        img.src = image.url;
                    });
                }
                thumb_uploader.open();
            });

            // Pause video whenever the thumbnail modal is dismissed (button or overlay).
            $thumbModal.on('click', '.vwg-modal-close, .vwg-thumb-done', function() {
                thumbModalVideo.pause();
                $thumbModal.hide();
            });
            $thumbModal.on('click', function(e) {
                if (e.target === this) {
                    thumbModalVideo.pause();
                    $thumbModal.hide();
                }
            });

        });
    </script>
    <?php
}
add_action( 'admin_footer-post.php', 'vwg_thumbnail_editor_assets' );
add_action( 'admin_footer-post-new.php', 'vwg_thumbnail_editor_assets' );
