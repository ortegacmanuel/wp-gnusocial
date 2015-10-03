( function( $ ) {
    /* private */

    function buildCard( data, $link ) {
        $link.on( "mouseenter", function() {
            setTimeout( function() {
                $( ".gs-hovercard" ).hide(); /* Only show one hovercard at a time */
                $link.data( "gs-hovercard" ).show();
            }, 400 );
        } );

        var offset = $link.offset(),
            group  = ( $link.text().charAt( 0 ) === "!" ),
            html   = buildHTML( data, group );

        html = $( html ).appendTo( "body" )
            .on( "mouseleave", function() {
                setTimeout( function() {
                    html.hide();
                }, 400 );
            } )
            .css( { top: offset.top, left: offset.left } );

        html.find( ".hc-follow" ).on( "click", function( e ) {
            e.preventDefault();
            $( this ).parent().find( ".hc-follow-form" ).slideToggle();
        } );

        html.find( "form" ).on( "submit", function( e ) {
            e.preventDefault();

            var $form   = $( this ),
                $input  = $form.find( "input[type=text]" ),
                profile = $input.val().split( "@" );

            if ( profile.length !== 2 ) {
                /* TODO: show error msg */
                $input.css( "border", "1px solid #f00" );
            } else {
                profile = profile[ 1 ];
                if ( !group ) {
                    $form.attr( "action", "http://" + profile + "/main/ostatussub" );
                } else {
                    $form.attr( "action", "http://" + profile + "/main/ostatusgroup" );
                }
                this.submit();
            }
        } );

        $link.data( "gs-hovercard", html ); /* Could use ARIA instead */
        $link.trigger( "mouseenter" );      /* Initial show() after we got the data */
    }

    function buildErrorCard() {
       /* TODO */
    }

    function getData() {

        var $this = $( this ),
            id    = $this.text(),
            group = ( $this.text().charAt( 0 ) === "!" ),
            patt  = $this.attr( "href" ).match('user') ? new RegExp( "user/.*" ) : new RegExp( id + "$" ),
            url   = $this.attr( "href" ).replace( patt, "" ),
            api   = group ? "/api/statusnet/groups/show.json?id=" + id : "/api/users/show.json?id=" + id;
         
        // NOTE:  Doesn't support api at non-default locations
        $.getJSON( url + api ) /* Fancy URL */
            .success( function( data ) {
                buildCard( data, $this );
            } )
            .error( function() { /* Try non-fancy URL */
                $.getJSON( url + "/index.php" + api )
                    .success( function( data ) {
                        buildCard( data, $this );
                    } )
                    .error( function() {
                        buildErrorCard();
                    } );
            } );
    }

    // public
    var methods = {
        init: function() {
            return this.each( function() {
                var $this = $( this ),
                    $collection;

                if ( $this.is( "a[href]" ) ) {
                    $collection = $this;
                } else {
                    $collection = $this.find( "a[href]" );
                }

                if ( $collection ) {
                    $collection.each( function() {

                        if ( $( this ).hasClass("h-card") ) {
                            $( this ).one( "mouseenter", getData );
                        } else if ( $( this ).hasClass("url") ) {
                            $( this ).one( "mouseenter", getData );    
                        } else if ( $( this ).text().match( /!\w+/ ) ) {
                            $( this ).one( "mouseenter", getData );
                        }
                    } );
                }
            } );
        }
    };

    // Method calling logic
    $.fn.gsHovercard = function( method ) {
        var elm;

        if ( methods[ method ] ) {
            elm = methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ) );
        } else if ( typeof method === "object" || !method ) {
            elm = methods.init.apply( this, arguments );
        } else {
            $.error( "Method " +  method + " does not exist on jQuery.gsHovercard" );
        }

        return elm;
    };

}( jQuery ) );
