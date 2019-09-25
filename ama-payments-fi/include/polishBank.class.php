<?php

/**
 * Polish bank payment class www.przelewy24.pl
 *
 * @package merituk
 * @author Art Media Agency LLC
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class polishBank
{

	/**
	 * Wsdl address
	 *
	 * @var $wsdl_address
	 **/
	protected $wsdl_address = 'https://secure.przelewy24.pl/external/wsdl/service.php?wsdl';


	/**
	 * url we send requests to
	 *
	 * @var $url
	 */
	protected $url = 'https://secure.przelewy24.pl/index.php';

	/**
	 * url we send requests to
	 *
	 * @var $sandbox_url
	 */
	protected $sandbox_url = 'https://sandbox.przelewy24.pl';

	/**
	 * submit button text
	 *
	 * @var $submit_btn_text
	 */
	protected $submit_button_text = 'Go to payment';

	/**
	 * unique order identifier must be different for every request
	 *
	 * @var $p24_session_id
	 */
	protected $p24_session_id;

	/**
	 * seller ID
	 *
	 * @var $p24_merchant_id
	 */
	protected $p24_merchant_id;

	/**
	 * order ID
	 *
	 * @var $p24_post_id
	 */
	protected $p24_pos_id;

	/**
	 * amount in Polish Grosz (PLN/100)
	 *
	 * @var $p24_amount
	 */
	protected $p24_amount;

    /**
	 * currency
	 *
	 * @var $p24_amount
	 */
    protected $p24_currency = 'PLN';

	/**
	 * client first name and surname
	 *
	 * @var $p24_client
	 */
	protected $p24_client;

	/**
	 * client street and home/apt. number
	 *
	 * @var $p24_address;
	 */
	protected $p24_address;

	/**
	 * client zip code
	 *
	 * @var $p24_zip
	 */
	protected $p24_zip;

	/**
	 * client city
	 *
	 * @var $p24_city
	 */
	protected $p24_city;

	/**
	 * client country code (D, PL, GB, A,...)
	 *
	 * @var $p24_kraj
	 */
	protected $p24_country= 'PL';

	/**
	 * client email
	 *
	 * @var $p24_email
	 */
	protected $p24_email;

    /**
	 * client phone
	 *
	 * @var $p24_phone
	 */
	protected $p24_phone;

	/**
	 * return URL address for correct transaction result (max.250 chars)
	 *
	 * @var $p24_url_return
	 */
	protected $p24_url_return;

	/**
	 * return URL address for incorrect transaction result (max. 250 chars)
	 *
	 * @var $p24_url_status
	 */
	protected $p24_url_status;

	/**
	 * extra description
	 *
	 * #var $p24_description
	 */
	protected $p24_description = ''; #TEST_ERR04

	/**
	 * language version PL/EN/DE/ES/IT
	 *
	 * @var $p24_language
	 */
	protected $p24_language = 'PL';

	/**
	 * input data hash (used to check if data are correct – see p. 2.1).
	 * Field used to calculate hash: p24_session_id, p24_id_sprzedawcy, p24_amount and CRC key
	 *
	 * @var $p24_crc
	 */
	protected $p24_crc;

	/**
	 * crc key for calculating p24_crc
	 *
	 * @var $p24_src_key
	 */
	protected $p24_crc_key;

   	/**
	 * Transaction key recived from p24
	 *
	 * @var $p24_order_id
	 */
    protected $p24_order_id;

    /**
     * @var $sandbox
     */
    protected $sandbox = false;


	/**
	 * form fields we use for request
	 *
	 * @var $formFields
	 */
	/*protected $formFields = array(
		'p24_session_id',
		'p24_id_sprzedawcy',
		'p24_order_id',
		'p24_kwota',
		'p24_klient',
		'p24_adres',
		'p24_kod',
		'p24_miasto',
		'p24_kraj',
		'p24_email',
		'p24_return_url_ok',
		'p24_return_url_error',
		'p24_opis',
		'p24_language',
		'p24_crc',
	);*/

    protected $formFields = array(
		'p24_session_id',
		'p24_merchant_id',
		'p24_pos_id',
		'p24_amount',
		'p24_currency',
		'p24_description',
		'p24_email',
        'p24_client',
        'p24_address',
        'p24_zip',
        'p24_city',
        'p24_country',
        'p24_phone',
		'p24_url_return',
		'p24_url_status',
		'p24_language',
		'p24_sign',
	);

    protected $verifyFields = array(
		'p24_session_id',
		'p24_merchant_id',
		'p24_pos_id',
		'p24_amount',
		'p24_currency',
		'p24_order_id',
		'p24_sign',
	);


	public function __construct($is_sandbox = false)
	{
	    $this->sandbox = $is_sandbox;
		if ( $is_sandbox ) $this->url = $this->sandbox_url;
	}


    /**
	 * Set p24_currency
	 *
	 * @param string $p24_currency
	 * @access public
	 * @return polishBank
	 */
	public function setP24Currency($p24_currency)
	{
		$this->p24_currency = $p24_currency;

		return $this;
	}


	/**
	 * Set p24_session_id
	 *
	 * @param string $p24_session_id
	 * @access public
	 * @return polishBank
	 */
	public function setP24SessionId($p24_session_id)
	{
		$this->p24_session_id = $p24_session_id;

		return $this;
	}

	/**
	 * Get p24_session_id
	 *
	 * @access public
	 * @return string
	 */
	public function getP24SessionId()
	{
		return $this->p24_session_id;
	}

	/**
	 * Set p24_pos_id
	 *
	 * @param string $p24_pos_id
	 * @access public
	 * @return polishBank
	 */
	public function setP24PosId($p24_pos_id)
	{
		$this->p24_pos_id = $p24_pos_id;

		return $this;
	}

	/**
	 * Get p24_order_id
	 *
	 * @access public
	 * @return string
	 */
	public function getP24PosId()
	{
		return $this->p24_pos_id;
	}

	/**
	 * Set p24_merchant_id
	 *
	 * @param string $p24_merchant_id
	 * @access public
	 * @return polishBank
	 */
	public function setP24MerchantId($p24_merchant_id)
	{
	    $this->setP24PosId($p24_merchant_id);
		$this->p24_merchant_id = $p24_merchant_id;

		return $this;
	}

	/**
	 * Get p24_id_sprzedawcy
	 *
	 * @access public
	 * @return string
	 */
	public function getP24MerchantId()
	{
		return $this->p24_merchant_id;
	}


    /**
	 * Set p24_order_id
	 *
	 * @param string $p24_order_id
	 * @access public
	 * @return polishBank
	 */
	public function setP24OrderId($p24_order_id)
	{
		$this->p24_order_id = $p24_order_id;

		return $this;
	}

	/**
	 * Get p24_order_id
	 *
	 * @access public
	 * @return string
	 */
	public function getP24OrderId()
	{
		return $this->p24_order_id;
	}

	/**
	 * Set p24_amount
	 *
	 * @param string $p24_amount
	 * @access public
	 * @return polishBank
	 */
	public function setP24Amount($p24_amount)
	{
		$this->p24_amount = number_format($p24_amount, 2, '.', '')*100;

		return $this;
	}

	/**
	 * Get p24_kwota
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Amount()
	{
		return $this->p24_amount;
	}


   	/**
	 * Set p24_description
	 *
	 * @param string $p24_description
	 * @access public
	 * @return polishBank
	 */
	public function setP24Description($p24_description)
	{
		$this->p24_description = $p24_description;

		return $this;
	}

	/**
	 * Get p24_description
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Description()
	{
		return $this->p24_description;
	}


	/**
	 * Set p24_client
	 *
	 * @param string $p24_client
	 * @access public
	 * @return polishBank
	 */
	public function setP24Client($p24_client)
	{
		$this->p24_client = $p24_client;

		return $this;
	}

	/**
	 * Get p24_client
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Client()
	{
		return $this->p24_client;
	}

	/**
	 * Set p24_address
	 *
	 * @param string $p24_address
	 * @access public
	 * @return polishBank
	 */
	public function setP24Address($p24_address)
	{
		$this->p24_address = $p24_address;

		return $this;
	}

	/**
	 * Get p24_address
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Address()
	{
		return $this->p24_address;
	}

	/**
	 * Set p24_zip
	 *
	 * @param string $p24_zip
	 * @access public
	 * @return polishBank
	 */
	public function setP24Zip($p24_zip)
	{
		$this->p24_zip = $p24_zip;

		return $this;
	}

	/**
	 * Get p24_zip
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Zip()
	{
		return $this->p24_zip;
	}

	/**
	 * Set p24_city
	 *
	 * @param string $p24_city
	 * @access public
	 * @return polishBank
	 */
	public function setP24City($p24_city)
	{
		$this->p24_city = $p24_city;

		return $this;
	}

	/**
	 * Get p24_city
	 *
	 * @access public
	 * @return string
	 */
	public function getP24City()
	{
		return $this->p24_city;
	}

	/**
	 * Set p24_email
	 *
	 * @param string $p24_email
	 * @access public
	 * @return polishBank
	 */
	public function setP24Email($p24_email)
	{
		$this->p24_email = $p24_email;

		return $this;
	}

	/**
	 * Get p24_email
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Email()
	{
		return $this->p24_email;
	}

    /**
	 * Set p24_phone
	 *
	 * @param string $p24_phone
	 * @access public
	 * @return polishBank
	 */
	public function setP24Phone($p24_phone)
	{
		$this->p24_phone = $p24_phone;

		return $this;
	}

	/**
	 * Get p24_phone
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Phone()
	{
		return $this->p24_phone;
	}

	/**
	 * Set p24_url_return
	 *
	 * @param string $p24_url_return
	 * @access public
	 * @return polishBank
	 */
	public function setP24UrlReturn($p24_url_return)
	{
		$this->p24_url_return = $p24_url_return;

		return $this;
	}

	/**
	 * Get p24_url_return
	 *
	 * @access public
	 * @return string
	 */
	public function getP24UrlReturn()
	{
		return $this->p24_url_return;
	}

	/**
	 * Set p24_url_status
	 *
	 * @param string $p24_url_status
	 * @access public
	 * @return polishBank
	 */
	public function setP24UrlStatus($p24_url_status)
	{
		$this->p24_url_status = $p24_url_status;

		return $this;
	}

	/**
	 * Get p24_url_status
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Urlstatus()
	{
		return $this->p24_url_status;
	}


	/**
	 * Set p24_language
	 *
	 * @param string $p24_language
	 * @access public
	 * @return polishBank
	 */
	public function setP24Language($p24_language)
	{
		$this->p24_language = $p24_language;

		return $this;
	}

	/**
	 * Get p24_language
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Language()
	{
		return $this->p24_language;
	}


	/**
	 * Set p24_crc
	 *
	 * @param string $p24_crc
	 * @access public
	 * @return polishBank
	 */
	public function setP24Crc($p24_crc)
	{
		$this->p24_crc = $p24_crc;

		return $this;
	}

	/**
	 * Get p24_crc
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Crc()
	{
		$srcCalc = array(
			$this->p24_session_id,
			$this->p24_merchant_id,
			$this->p24_amount,
            $this->p24_currency,
			$this->p24_crc_key
		);
		return md5(implode("|", $srcCalc));
	}

   	/**
	 * Get p24_sign
	 *
	 * @access public
	 * @return string
	 */
	public function getP24Sign()
	{
		$srcCalc = array(
			$this->p24_session_id,
			$this->p24_order_id,
			$this->p24_amount,
            $this->p24_currency,
			$this->p24_crc_key
		);
		return md5(implode("|", $srcCalc));
	}



	/**
	 * Set p24_crc_key
	 *
	 * @param string $p24_crc_key
	 * @access public
	 * @return polishBank
	 */
	public function setP24CrcKey($p24_crc_key)
	{
		$this->p24_crc_key = $p24_crc_key;

		return $this;
	}

	/**
	 * Get p24_crc_key
	 *
	 * @access public
	 * @return string
	 */
	public function getP24CrcKey()
	{
		return $this->p24_crc_key;
	}

	/**
	 * Set submit button text
	 *
	 * @param $sumbit_button_text
	 * @acces public
	 * @return polishBank
	 */
	public function setSubmitButtonText($submit_button_text)
	{
		$this->submit_button_text = $submit_button_text;

		return $this;
	}

	/**
	 * Generate form
	 *
	 * @access public
	 * @return string
	 */
	public function getForm()
	{
		$form = '<form action="' . $this->url . '" method="post"  id="paymentForm">';

		foreach ( $this->formFields as $field ) {
			if ( $field == "p24_crc" ) $this->{$field} = $this->getP24Crc();
			$form .= '<input type="hidden" name="'.$field.'" value="'.$this->{$field}.'" />';
		}
		$form .= '<input type="submit" name="submit_send" class="btn btn-primary" value="' . $this->submit_button_text . '">';
		//$form .= '<input type="hidden" name="p24_metoda" value="" id="p24_method">';
		$form .= '</form>';



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
	}

	/**
	 * Generate random session id using order id
	 *
	 * @param $orderId
	 * @access public
	 * @return string
	 */
	public function generateRandSessionId($orderId)
	{
		$time = mktime();
		return substr($time, (strlen($time)-4),4) . $orderId;
	}

	/**
	 * Get id from session Id
	 *
	 * @access public
	 * @return string
	 */
	public function getIdFromSessionId()
	{
		$id = substr($this->p24_session_id, 4, strlen($this->p24_session_id));
		return $id;
	}

	/**
	 * Get payment methods
	 */
	public function getPaymentMethods()
	{
		$soap = new SoapClient($this->wsdl_address);
		$res = $soap->PaymentMethods(WSDL_USER, WSDL_PASSWORD, WSDL_LANG);
		if ($res->error->errorCode) {
			error_log($res->error->errorMessage);
		}

		return $res->result;
	}

    /**
     * Go to payment
     */
    public function goToPayment()
    {
        include_once 'class_przelewy24.php';
        $P24 = new Przelewy24($this->p24_merchant_id,$this->p24_pos_id, $this->p24_crc_key, $this->sandbox);
        $RET = $P24->testConnection();
        if($RET['error']!=0) {
            return $RET['errorMessage'];
        }
        //return true;

        $P24 = $this->setGotToPaymentValues($P24);
        $RET = $P24->trnRegister(true);
        if($RET['error']!=0) {
            return $RET["errorMessage"];
        }

        return true;
    }

    /**
     * Set values for payment
     */
    public function setGotToPaymentValues($P24) {
        foreach ( $this->formFields as $field ) {
            $P24->addValue($field, $this->{$field});
        }
        return $P24;
    }


    /**
     * Verify payment
     */
    public function verifyPayment()
    {

        include_once 'class_przelewy24.php';
        $P24 = new Przelewy24($this->p24_merchant_id,$this->p24_pos_id, $this->p24_crc_key, $this->sandbox);
        $P24 = $this->setVerifyPaymentValues($P24);

        $RET = $P24->trnVerify();
        if($RET['error']!=0) {
            return false;
        }

        return true;
    }

    /**
     * Set values for verify payment
     * @param $P24
     */
    public function setVerifyPaymentValues($P24) {
        foreach ( $this->verifyFields as $field ) {
            $P24->addValue($field, $this->{$field});
        }
        $P24->addValue('p24_sign', $p24_sign = $this->getP24Sign());
        return $P24;
    }

}



?>