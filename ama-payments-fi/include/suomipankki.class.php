<?php

class suomipankki {

	/**
	 * Merchant id default is 13466
	 */
	public $MERCHANT_ID = 13466;

	/**
	 * Merchant secret
	 */
	public $MERCHANT_SECRET = '';


	/**
	 * We hold amount we going to pay
	 */
	public $AMOUNT;

	/**
	 * Every order has to have its unique id
	 */
	public $ORDER_NUMBER;

	public $REFERENCE_NUMBER;
	public $ORDER_DESCRIPTION;

	/**
	 * Curreny default is EUR
	 */
	public $CURRENCY = 'EUR';

	/**
	 * Where we return after payment is successful
	 */
	public $RETURN_ADDRESS;

	/**
	 * Where we return after payment is canceled
	 */
	public $CANCEL_ADDRESS;

	/**
	 * Pending address can be empty
	 */
	public $PENDING_ADDRESS = '';


	public $NOTIFY_ADDRESS;
	public $TYPE    = "S1";
	public $CULTURE = "fi_FI";
	public $PRESELECTED_METHOD;
	public $MODE    = 1;
	public $VISIBLE_METHODS;
	public $GROUP;

	/**
	 * This is the auth code with what system will recognize us.
	 */
	public $AUTHCODE;


	public $FORM_ACTION = 'https://payment.verkkomaksut.fi/';


	/**
	 * Fields used for authcode
	 */
	private $usedForAuthcode = array(
	'MERCHANT_SECRET',
	'MERCHANT_ID',
	'AMOUNT',
	'ORDER_NUMBER',
	'REFERENCE_NUMBER',
	'ORDER_DESCRIPTION',
	'CURRENCY',
	'RETURN_ADDRESS',
	'CANCEL_ADDRESS',
	'PENDING_ADDRESS',
	'NOTIFY_ADDRESS',
	'TYPE',
	'CULTURE',
	'PRESELECTED_METHOD',
	'MODE',
	'VISIBLE_METHODS',
	'GROUP'
	);

	/**
	 * Form fields
	 */
	private $formFields = array(
	'MERCHANT_ID',
	'AMOUNT',
	'ORDER_NUMBER',
	'REFERENCE_NUMBER',
	'ORDER_DESCRIPTION',
	'CURRENCY',
	'RETURN_ADDRESS',
	'CANCEL_ADDRESS',
	'PENDING_ADDRESS',
	'NOTIFY_ADDRESS',
	'TYPE',
	'CULTURE',
	'MODE',
	'VISIBLE_METHODS',
	'AUTHCODE'
	);


	/**
	 * Generate authcode for form
	 *
	 * @return string
	 */
	private function generateAuthCode()
	{
		foreach ( $this->usedForAuthcode as $var ) {
			$hashValues[] = $this->{$var};
		}
		return strtoupper(md5(implode("|", $hashValues)));
	}


	/**
	 * Return finished form
	 *
	 * @return string
	 */
	public function getForm()
	{

		$form = '<form action="" method="post"  id="paymentForm">';

		foreach ( $this->formFields as $field ) {
			if ( $field == "AUTHCODE" ) $this->{$field} = $this->generateAuthCode();
			$form .= '<input type="hidden" name="'.$field.'" value="'.$this->{$field}.'" />';
		}
		$form .= "</form>
		<script type=\"text/javascript\">
				jQuery.getScript( '//payment.verkkomaksut.fi/js/payment-widget-v1.0.min.js', function( data, textStatus, jqxhr ) {
				  	SV.widget.initWithForm('paymentForm', {
						charset:'UTF-8', defaultLocale: 'fi_FI', debug: 1, width: '870'
					});
				});
		</script>
		";

		return $form;
	}


	/**
	 * Return reference
	 *
	 * @param stamp
	 * @return int
	 */
	public function generateReference($stamp)
	{
		$stamp = strval($stamp);
		$multipliers = Array(7, 3, 1);
        $result = Array();
        $sum = 0;
        $currmult = 0;
        for ($i = strlen($stamp) - 1; $i >= 0; $i--)
        {
            $digit = $stamp{$i};
            if (! is_numeric($digit))
            {
                continue;
            }
            $digit = (int) $digit;
            $sum += $digit * $multipliers[$currmult];
            $currmult = ($currmult == 2) ? 0 : $currmult + 1;
        }
        // Get the difference to the next highest ten
        $nextten = (((int) ($sum / 10)) + 1) * 10;
        $checkdigit = $nextten - $sum;
        if ($checkdigit == 10)
        {
            $checkdigit = 0;
        }
        return $stamp.$checkdigit;
	}// end function


}

?>