import { PAYMENT_STORE_KEY } from '@woocommerce/block-data'; // "wc/store/payment"
import { extensionCartUpdate } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';
import { subscribe, select } from '@wordpress/data';

let previouslyChosenPaymentMethod = getSetting(
	'persistedChosenPaymentMethod',
	''
);

subscribe( function () {
	const chosenPaymentMethod =
		select( PAYMENT_STORE_KEY ).getActivePaymentMethod();
	const selectedActiveToken =
		select( PAYMENT_STORE_KEY ).getActiveSavedToken();

	if ( selectedActiveToken !== '' ) {
		previouslyChosenPaymentMethod = '';
		extensionCartUpdate( {
			namespace: 'persisted-chosen-payment-method',
			data: { method: '' },
		} );
		return;
	}

	if ( chosenPaymentMethod !== previouslyChosenPaymentMethod ) {
		previouslyChosenPaymentMethod = chosenPaymentMethod;
		extensionCartUpdate( {
			namespace: 'persisted-chosen-payment-method',
			data: { method: chosenPaymentMethod },
		} );
	}
}, PAYMENT_STORE_KEY );
