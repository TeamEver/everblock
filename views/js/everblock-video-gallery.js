$(document).ready(function () {
    $('.everblock-video-gallery').each(function () {
        var $gallery = $(this);
        var blockId = $gallery.closest('[id^="block-"]').attr('id').replace('block-', '');
        var $modal = $('#videoModal-' + blockId);
        $gallery.find('.everblock-video-item').on('click', function (e) {
            e.preventDefault();
            var src = $(this).data('video');
            $modal.find('iframe').attr('src', src);
            $modal.modal('show');
        });
        $modal.on('hidden.bs.modal', function () {
            $modal.find('iframe').attr('src', '');
        });
        if ($gallery.data('carousel') == 1 && typeof $.fn.slick !== 'undefined') {
            $gallery.find('.everblock-video-container').slick();
        }
    });
});
