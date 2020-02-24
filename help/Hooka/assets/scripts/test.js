jQuery( function( $ ) {
    console.log('fjshf hsdh isdh usd gsjg fjdsi');

    $( document.body ).on( 'updated_checkout', updatedHendler );

    function updatedHendler(e) {
        console.log(e);
    }
});