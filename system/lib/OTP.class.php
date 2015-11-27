<?php
/**
 * OTP Class, we will build either totp or hotp off of this
 *
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   Wayne Oliver <wayne@open-is.co.za> 2011 - 2015
 * @license     BSD
 ********************************** 80 Columns *********************************
 **/

use Base32\Base32;
  
class OTP {
  // This class is heavily inspired by and in some case copid verbatim from
  // https://github.com/lelag/otphp
  // It's a really stripped down version.



  public $skey;
  public $algo;
  public $num;


  public function __construct($secret, $options = Array()) {

    $this->num  = isset($options['num']) ? $option['num'] : 6;
    $this->algo = isset($options['algo']) ? $option['algo'] : 'sha1';
    $this->skey = $secret;

  }


  // get the otp
  public function get_otp($input) {

    $hash = hash_hmac($this->algo, $this->int_to_byte_string($input),$this->base32_key());
    foreach(str_split($hash, 2) as $hex) { // stupid PHP has bin2hex but no hex2bin WTF TODO:fix
      $hmac[] = hexdec($hex);
    }
    $offset = $hmac[19] & 0xf;
    $code = ($hmac[$offset+0] & 0x7F) << 24 |
        ($hmac[$offset + 1] & 0xFF) << 16 |
        ($hmac[$offset + 2] & 0xFF) << 8 |
        ($hmac[$offset + 3] & 0xFF);
    return $code % pow(10, $this->num);
  }

  
  public function base32_key() {
    return Base32::decode($this->skey);
  }

  /**
   * Turns an integer in a OATH bytestring
   * @param integer $int
   * @access private
   * @return string bytestring
   */
  public function int_to_byte_string($int) {
    $result = Array();
    while($int != 0) {
      $result[] = chr($int & 0xFF);
      $int >>= 8;
    }
    return str_pad(join(array_reverse($result)), 8, "\000", STR_PAD_LEFT);
  }
}
