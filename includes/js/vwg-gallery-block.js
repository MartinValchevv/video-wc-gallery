/**
 * Video Gallery for WooCommerce - Product Gallery Block Support
 *
 * Handles video playback integration with the WooCommerce Product Gallery Block
 * (Interactivity API-based gallery). Uses an "Image Impostor" strategy where
 * video thumbnails are inserted as regular <img> elements for native navigation,
 * then Video.js players are overlaid when the slide becomes active.
 *
 * @since 2.5
 */
(function () {
    'use strict';

    var settings = window.vwgBlockData || {};
    var activePlayers = {};
    var dialogPlayers = {};

    /**
     * Initialize all product gallery blocks on the page.
     */
    function init() {
        var galleries = document.querySelectorAll('[data-wp-interactive="woocommerce/product-gallery"]');
        for (var i = 0; i < galleries.length; i++) {
            initGallery(galleries[i]);
        }
    }

    /**
     * Initialize a single product gallery block.
     *
     * @param {HTMLElement} galleryEl The gallery root element.
     */
    function initGallery(galleryEl) {
        var contextStr = galleryEl.getAttribute('data-wp-context');
        if (!contextStr) {
            return;
        }

        var context;
        try {
            context = JSON.parse(contextStr);
        } catch (e) {
            return;
        }

        var videoData = context.vwgVideos;
        if (!videoData || Object.keys(videoData).length === 0) {
            return;
        }

        // Mark the gallery as having videos.
        galleryEl.setAttribute('data-vwg-has-videos', 'true');

        // Setup large image video slides.
        setupLargeImageSlides(galleryEl, videoData);

        // Setup visibility/scroll detection.
        setupScrollDetection(galleryEl, videoData);

        // Setup dialog video handling.
        setupDialogHandling(galleryEl, videoData);
    }

    /**
     * Setup click handlers and markers on video slides in the large image area.
     *
     * @param {HTMLElement} galleryEl The gallery root element.
     * @param {Object}      videoData Video metadata from context.
     */
    function setupLargeImageSlides(galleryEl, videoData) {
        var container = galleryEl.querySelector('.wc-block-product-gallery-large-image__container');
        if (!container) {
            return;
        }

        var videoImages = container.querySelectorAll('img[data-vwg-video-src]');
        for (var i = 0; i < videoImages.length; i++) {
            setupSingleVideoSlide(videoImages[i], videoData, galleryEl);
        }
    }

    /**
     * Setup a single video slide's click handler and interaction prevention.
     *
     * @param {HTMLImageElement} imgEl     The poster image element.
     * @param {Object}           videoData Video metadata from context.
     * @param {HTMLElement}       galleryEl The gallery root element.
     */
    function setupSingleVideoSlide(imgEl, videoData, galleryEl) {
        var syntheticId = imgEl.getAttribute('data-image-id');
        var videoInfo = videoData[syntheticId];
        if (!videoInfo) {
            return;
        }

        var li = imgEl.closest('.wc-block-product-gallery-large-image__wrapper');
        if (!li) {
            return;
        }

        // Store video info on the element for later access.
        li.setAttribute('data-vwg-video-url', videoInfo.url);
        li.setAttribute('data-vwg-video-thumb', videoInfo.thumb);

        // Remove zoom and fullscreen classes from the poster image.
        imgEl.classList.remove(
            'wc-block-woocommerce-product-gallery-large-image__image--hoverZoom',
            'wc-block-woocommerce-product-gallery-large-image__image--full-screen-on-click'
        );

        // Prevent zoom mousemove handler.
        imgEl.addEventListener('mousemove', function (e) {
            e.stopPropagation();
            imgEl.style.transform = '';
            imgEl.style.transformOrigin = '';
        }, true);

        // Prevent mouseleave from trying to reset zoom.
        imgEl.addEventListener('mouseleave', function (e) {
            e.stopPropagation();
        }, true);

        // Click handler: activate video player instead of opening dialog.
        imgEl.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            activateVideoPlayer(li, videoInfo, galleryEl);
        });
    }

    /**
     * Create or show a Video.js player overlay on a video slide.
     *
     * @param {HTMLElement} li         The <li> slide element.
     * @param {Object}      videoInfo  Video metadata { url, thumb }.
     * @param {HTMLElement}  galleryEl  The gallery root element.
     */
    function activateVideoPlayer(li, videoInfo, galleryEl) {
        var existingContainer = li.querySelector('.vwg-block-video-container');

        if (existingContainer) {
            // Player already exists — show it and play.
            existingContainer.style.display = '';
            var existingVideo = existingContainer.querySelector('video');
            if (existingVideo && typeof videojs !== 'undefined') {
                try {
                    var player = videojs(existingVideo.id);
                    player.play();
                } catch (e) {
                    // Player may have been disposed.
                }
            }
            return;
        }

        // Check if this is a YouTube URL.
        if (isYouTubeUrl(videoInfo.url)) {
            createYouTubePlayer(li, videoInfo);
            return;
        }

        // Create Video.js player.
        createVideoJsPlayer(li, videoInfo, galleryEl);
    }

    /**
     * Create a Video.js player for a self-hosted video.
     *
     * @param {HTMLElement} li        The <li> slide element.
     * @param {Object}      videoInfo Video metadata.
     * @param {HTMLElement}  galleryEl The gallery root element.
     */
    function createVideoJsPlayer(li, videoInfo, galleryEl) {
        var adaptClass = settings.adaptSizes ? '' : 'vjs-fluid';
        var videoId = 'vwg_block_video_' + Math.random().toString(36).substr(2, 9);

        var container = document.createElement('div');
        container.className = 'vwg-block-video-container';

        var videoEl = document.createElement('video');
        videoEl.id = videoId;
        videoEl.className = 'video-js ' + adaptClass + ' vwg_video_js';
        videoEl.setAttribute('preload', 'auto');
        videoEl.setAttribute('playsinline', '');
        videoEl.setAttribute('crossorigin', 'anonymous');
        videoEl.setAttribute('poster', videoInfo.thumb);
        videoEl.setAttribute('data-setup', '{}');
        videoEl.setAttribute('data-video-url', videoInfo.url);

        if (settings.controls) {
            videoEl.setAttribute('controls', '');
        }
        if (settings.loop) {
            videoEl.setAttribute('loop', '');
        }
        if (settings.muted) {
            videoEl.setAttribute('muted', '');
        }

        var source = document.createElement('source');
        source.src = videoInfo.url;
        source.type = 'video/mp4';
        videoEl.appendChild(source);

        container.appendChild(videoEl);

        // Insert the video container into the slide.
        var productImage = li.querySelector('.wc-block-components-product-image');
        if (productImage) {
            productImage.style.position = 'relative';
            productImage.appendChild(container);
        } else {
            li.appendChild(container);
        }

        // Initialize Video.js.
        if (typeof videojs !== 'undefined') {
            var player = videojs(videoId, {}, function () {
                this.play();
            });

            // Store reference for cleanup.
            var galleryId = galleryEl.getAttribute('data-vwg-gallery-id') ||
                ('gallery_' + Math.random().toString(36).substr(2, 6));
            galleryEl.setAttribute('data-vwg-gallery-id', galleryId);

            if (!activePlayers[galleryId]) {
                activePlayers[galleryId] = {};
            }
            activePlayers[galleryId][videoId] = player;

            // PRO Analytics: Attach tracking events to the dynamically created player.
            attachAnalyticsTracking(player, videoEl);
        }
    }

    /**
     * Create a YouTube iframe player.
     *
     * @param {HTMLElement} li        The <li> slide element.
     * @param {Object}      videoInfo Video metadata.
     */
    function createYouTubePlayer(li, videoInfo) {
        var container = document.createElement('div');
        container.className = 'vwg-block-video-container vwg-block-youtube-container';

        var iframe = document.createElement('iframe');
        iframe.src = getYouTubeEmbedUrl(videoInfo.url);
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
        iframe.setAttribute('frameborder', '0');
        iframe.style.width = '100%';
        iframe.style.height = '100%';

        container.appendChild(iframe);

        var productImage = li.querySelector('.wc-block-components-product-image');
        if (productImage) {
            productImage.style.position = 'relative';
            productImage.appendChild(container);
        } else {
            li.appendChild(container);
        }
    }

    /**
     * Pause all active video players in a gallery (except the specified one).
     *
     * @param {HTMLElement} galleryEl     The gallery root element.
     * @param {string|null} exceptVideoId Video ID to skip pausing (optional).
     */
    function pauseAllPlayers(galleryEl, exceptVideoId) {
        var galleryId = galleryEl.getAttribute('data-vwg-gallery-id');
        if (!galleryId || !activePlayers[galleryId]) {
            return;
        }

        var players = activePlayers[galleryId];
        for (var id in players) {
            if (players.hasOwnProperty(id) && id !== exceptVideoId) {
                try {
                    players[id].pause();
                } catch (e) {
                    // Player may have been disposed.
                }
            }
        }
    }

    /**
     * Hide video containers on slides that are not visible.
     *
     * @param {HTMLElement} galleryEl The gallery root element.
     */
    function hideInactiveVideoContainers(galleryEl) {
        var container = galleryEl.querySelector('.wc-block-product-gallery-large-image__container');
        if (!container) {
            return;
        }

        var slides = container.querySelectorAll('.vwg-block-video-slide');
        for (var i = 0; i < slides.length; i++) {
            var slide = slides[i];
            var videoContainer = slide.querySelector('.vwg-block-video-container');
            if (!videoContainer) {
                continue;
            }

            // Check if the slide is currently visible by comparing scroll position.
            var slideRect = slide.getBoundingClientRect();
            var containerRect = container.getBoundingClientRect();
            var isVisible = (
                slideRect.left < containerRect.right &&
                slideRect.right > containerRect.left &&
                slideRect.width > 0
            );

            if (!isVisible) {
                // Pause and hide the video.
                var videoEl = videoContainer.querySelector('video');
                if (videoEl && typeof videojs !== 'undefined') {
                    try {
                        var player = videojs(videoEl.id);
                        player.pause();
                    } catch (e) {
                        // Ignore.
                    }
                }
                videoContainer.style.display = 'none';
            }
        }
    }

    /**
     * Setup scroll detection for the large image container.
     * Uses IntersectionObserver to detect when video slides enter/leave the viewport.
     *
     * @param {HTMLElement} galleryEl The gallery root element.
     * @param {Object}      videoData Video metadata.
     */
    function setupScrollDetection(galleryEl, videoData) {
        var container = galleryEl.querySelector('.wc-block-product-gallery-large-image__container');
        if (!container) {
            return;
        }

        var videoSlides = container.querySelectorAll('.vwg-block-video-slide');
        if (videoSlides.length === 0) {
            return;
        }

        // Use IntersectionObserver to detect visible slides.
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                for (var i = 0; i < entries.length; i++) {
                    var entry = entries[i];
                    var slide = entry.target;

                    if (!entry.isIntersecting) {
                        // Slide left the viewport — pause and hide video.
                        var videoContainer = slide.querySelector('.vwg-block-video-container');
                        if (videoContainer) {
                            var videoEl = videoContainer.querySelector('video');
                            if (videoEl && typeof videojs !== 'undefined') {
                                try {
                                    videojs(videoEl.id).pause();
                                } catch (e) {
                                    // Ignore.
                                }
                            }
                            videoContainer.style.display = 'none';
                        }
                    } else if (entry.isIntersecting && entry.intersectionRatio > 0.5) {
                        // Slide entered the viewport.
                        if (settings.autoplay) {
                            var url = slide.getAttribute('data-vwg-video-url');
                            var thumb = slide.getAttribute('data-vwg-video-thumb');
                            if (url) {
                                activateVideoPlayer(slide, { url: url, thumb: thumb }, galleryEl);
                            }
                        }
                    }
                }
            }, {
                root: container,
                threshold: [0, 0.5]
            });

            for (var i = 0; i < videoSlides.length; i++) {
                observer.observe(videoSlides[i]);
            }
        }

        // Also listen for scroll events as a fallback.
        container.addEventListener('scroll', debounce(function () {
            hideInactiveVideoContainers(galleryEl);
        }, 200));

        // Listen for thumbnail clicks to manage video visibility.
        galleryEl.addEventListener('click', function (e) {
            var thumbnailImg = e.target.closest('.wc-block-product-gallery-thumbnails__thumbnail__image');
            if (thumbnailImg) {
                // Short delay to allow interactivity API to process the selection first.
                setTimeout(function () {
                    hideInactiveVideoContainers(galleryEl);
                }, 100);
            }
        });
    }

    /**
     * Setup dialog (fullscreen) video handling.
     * When the dialog opens, replace video poster images with actual video players.
     *
     * @param {HTMLElement} galleryEl The gallery root element.
     * @param {Object}      videoData Video metadata.
     */
    function setupDialogHandling(galleryEl, videoData) {
        var dialog = galleryEl.querySelector('.wc-block-product-gallery-dialog');
        if (!dialog) {
            return;
        }

        var dialogObserver = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].attributeName === 'open') {
                    if (dialog.hasAttribute('open')) {
                        onDialogOpen(dialog, videoData);
                    } else {
                        onDialogClose(dialog);
                    }
                }
            }
        });

        dialogObserver.observe(dialog, { attributes: true, attributeFilter: ['open'] });
    }

    /**
     * Handle dialog opening — create video players for video poster images.
     *
     * @param {HTMLElement} dialog    The dialog element.
     * @param {Object}      videoData Video metadata.
     */
    function onDialogOpen(dialog, videoData) {
        var dialogContent = dialog.querySelector('.wc-block-product-gallery-dialog__content');
        if (!dialogContent) {
            return;
        }

        var videoPosterImgs = dialogContent.querySelectorAll('img[data-vwg-video-src]');
        for (var i = 0; i < videoPosterImgs.length; i++) {
            var img = videoPosterImgs[i];
            if (img.nextElementSibling && img.nextElementSibling.classList.contains('vwg-block-video-container')) {
                // Already has a video player.
                continue;
            }

            var videoUrl = img.getAttribute('data-vwg-video-src');
            var thumbUrl = img.getAttribute('src');

            if (isYouTubeUrl(videoUrl)) {
                createDialogYouTubePlayer(img, videoUrl);
            } else {
                createDialogVideoJsPlayer(img, videoUrl, thumbUrl);
            }
        }
    }

    /**
     * Create a Video.js player in the dialog for a video poster image.
     *
     * @param {HTMLImageElement} img      The poster image in the dialog.
     * @param {string}           videoUrl The video URL.
     * @param {string}           thumbUrl The thumbnail URL.
     */
    function createDialogVideoJsPlayer(img, videoUrl, thumbUrl) {
        var adaptClass = settings.adaptSizes ? '' : 'vjs-fluid';
        var videoId = 'vwg_dialog_video_' + Math.random().toString(36).substr(2, 9);

        var container = document.createElement('div');
        container.className = 'vwg-block-video-container vwg-block-dialog-video';

        var videoEl = document.createElement('video');
        videoEl.id = videoId;
        videoEl.className = 'video-js ' + adaptClass + ' vwg_video_js';
        videoEl.setAttribute('preload', 'auto');
        videoEl.setAttribute('playsinline', '');
        videoEl.setAttribute('crossorigin', 'anonymous');
        videoEl.setAttribute('poster', thumbUrl);
        videoEl.setAttribute('data-setup', '{}');
        videoEl.setAttribute('data-video-url', videoUrl);

        if (settings.controls) {
            videoEl.setAttribute('controls', '');
        }
        if (settings.loop) {
            videoEl.setAttribute('loop', '');
        }
        if (settings.muted) {
            videoEl.setAttribute('muted', '');
        }

        var source = document.createElement('source');
        source.src = videoUrl;
        source.type = 'video/mp4';
        videoEl.appendChild(source);

        container.appendChild(videoEl);

        // Insert after the poster image.
        img.style.display = 'none';
        img.parentNode.insertBefore(container, img.nextSibling);

        if (typeof videojs !== 'undefined') {
            var player = videojs(videoId);
            dialogPlayers[videoId] = player;

            // PRO Analytics: Attach tracking events to the dialog player.
            attachAnalyticsTracking(player, videoEl);
        }
    }

    /**
     * Create a YouTube iframe player in the dialog.
     *
     * @param {HTMLImageElement} img      The poster image in the dialog.
     * @param {string}           videoUrl The YouTube URL.
     */
    function createDialogYouTubePlayer(img, videoUrl) {
        var container = document.createElement('div');
        container.className = 'vwg-block-video-container vwg-block-dialog-video vwg-block-youtube-container';

        var iframe = document.createElement('iframe');
        iframe.src = getYouTubeEmbedUrl(videoUrl);
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
        iframe.setAttribute('frameborder', '0');
        iframe.style.width = '100%';
        iframe.style.height = '100%';

        container.appendChild(iframe);

        img.style.display = 'none';
        img.parentNode.insertBefore(container, img.nextSibling);
    }

    /**
     * Handle dialog closing — dispose dialog video players.
     *
     * @param {HTMLElement} dialog The dialog element.
     */
    function onDialogClose(dialog) {
        for (var id in dialogPlayers) {
            if (dialogPlayers.hasOwnProperty(id)) {
                try {
                    dialogPlayers[id].dispose();
                } catch (e) {
                    // Ignore.
                }
            }
        }
        dialogPlayers = {};

        // Remove dialog video containers and show poster images again.
        var dialogVideos = dialog.querySelectorAll('.vwg-block-dialog-video');
        for (var i = 0; i < dialogVideos.length; i++) {
            var container = dialogVideos[i];
            var prevImg = container.previousElementSibling;
            if (prevImg && prevImg.tagName === 'IMG') {
                prevImg.style.display = '';
            }
            container.parentNode.removeChild(container);
        }
    }

    // --- Analytics Integration (PRO) ---

    /**
     * Attach PRO analytics tracking to a dynamically created Video.js player.
     *
     * The PRO plugin exposes window.VWGAnalytics with an attachVideoEvents method
     * that binds play/pause/ended/timeupdate/seek/volume/fullscreen event listeners.
     * Since the analytics tracker only scans for videos once at 500ms after page load,
     * dynamically created block gallery videos need to be registered manually.
     *
     * @param {Object}      player   The Video.js player instance.
     * @param {HTMLElement}  videoEl  The <video> DOM element.
     */
    function attachAnalyticsTracking(player, videoEl) {
        if (window.VWGAnalytics && typeof window.VWGAnalytics.attachVideoEvents === 'function') {
            try {
                window.VWGAnalytics.attachVideoEvents(player, videoEl);
            } catch (e) {
                // PRO analytics not available or error — fail silently.
            }
        }
    }

    // --- Utility Functions ---

    /**
     * Check if a URL is a YouTube URL.
     *
     * @param {string} url The URL to check.
     * @return {boolean} True if YouTube URL.
     */
    function isYouTubeUrl(url) {
        return /(?:youtube\.com|youtu\.be)/i.test(url);
    }

    /**
     * Convert a YouTube URL to an embed URL.
     *
     * @param {string} url The YouTube URL.
     * @return {string} The embed URL.
     */
    function getYouTubeEmbedUrl(url) {
        var match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/);
        if (match && match[1]) {
            var params = '?enablejsapi=1';
            if (settings.autoplay) {
                params += '&autoplay=1';
            }
            if (settings.muted) {
                params += '&mute=1';
            }
            if (settings.loop) {
                params += '&loop=1&playlist=' + match[1];
            }
            if (!settings.controls) {
                params += '&controls=0';
            }
            return 'https://www.youtube.com/embed/' + match[1] + params;
        }
        return url;
    }

    /**
     * Simple debounce function.
     *
     * @param {Function} func  The function to debounce.
     * @param {number}   delay Delay in milliseconds.
     * @return {Function} Debounced function.
     */
    function debounce(func, delay) {
        var timer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                func.apply(context, args);
            }, delay);
        };
    }

    // --- Bootstrap ---

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
