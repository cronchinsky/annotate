$(function () {
  $('.annotate-sample-image-wrapper a').click(function (e) {
    viewLargerImage($(this));
    return false;
  });
});


 function viewLargerImage( $link ) {
        var src = $link.attr( "href" ),
        title = $link.siblings( "img" ).attr( "alt" ),
        $modal = $( "img[src$='" + src + "']" ).find('.annotate-modal-image');

        if ( $modal.length ) {
            $modal.dialog( "open" );
            $modal.dialog("option", "position", "top");
        } else {
            var img = $( "<img class='annotate-modal-image' alt='" + title + "' style='display: none; padding: 8px;' />" )
            .attr( "src", src ).appendTo( "body" );
            img.load( function () {
                $(this).dialog({
                    title: $(this).attr('alt'),
                    width: $(this).width()+16,
                    modal: true
                });
                $(this).dialog("option", "position", "top");
            });


        }
    }