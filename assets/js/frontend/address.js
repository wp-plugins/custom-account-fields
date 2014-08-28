jQuery( function( $ ) {
	$( 'body' )

		.bind( 'country_to_state_changing', function( event, country, wrapper ) {

		var $vat_number_2_box = $( this ).find( '#billing_vat_number_2_field' ).closest( 'p' ),
		$billing_fields = wrapper.parent().find( '.woocommerce-billing-fields, .woocommerce' );
		if( $billing_fields.length > 0)
		{
			if( country === 'SK' ) {
				$vat_number_2_box.show();
			}
			else {
				$vat_number_2_box.hide();
			}
		}	
	});

});
