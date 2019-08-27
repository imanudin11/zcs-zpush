<?php
/***********************************************
* File          :   ZimbraHttpStreamWrapper.php
* Revision      :   3 (18-Dec-2016)
* Project       :   Z-Push Zimbra Backend
*                   http://sourceforge.net/projects/zimbrabackend
* Description   :   Stream Wrapper class to wrap a standard php http stream and to provide the length of the file for download from zimbra
*
* Copyright     :   Vincent Sherwood
************************************************

The zimbra nginx server does not provide a Content-Length header for a standard http stream call. This class is designed to wrap the standard HTTP
stream as well as to provide the length of the data to be downloaded which is required for the processMultipart function in Z-Push's wbxmlencoder class.

Changed for Revision 3
 - Added options for verifying the SSL Peer and HostName

Changed for Revision 2
 - Fixed type on stream_stat debug message

************************************************/

class ZimbraHttpStreamWrapper {
    const PROTOCOL = "zimbrahttpstream";

    private $zimbrahttpstream;
    private $position;
    private $zimbrahttpstreamlength;

    /**
     * Opens the stream
     * The mapistream reference is passed over the context
     *
     * @param string    $path           Specifies the URL that was passed to the original function
     * @param string    $mode           The mode used to open the file, as detailed for fopen()
     * @param int       $options        Holds additional flags set by the streams API
     * @param string    $opened_path    If the path is opened successfully, and STREAM_USE_PATH is set in options,
     *                                  opened_path should be set to the full path of the file/resource that was actually opened.
     *
     * @access public
     * @return boolean
     */
    public function stream_open($path, $mode, $options, &$opened_path) {
		
    try {

        $contextOptions = stream_context_get_options($this->context);

        if (!isset($contextOptions[self::PROTOCOL]['publicUrl']) || !isset($contextOptions[self::PROTOCOL]['authToken']) || !isset($contextOptions[self::PROTOCOL]['id']) || !isset($contextOptions[self::PROTOCOL]['part']) || !isset($contextOptions[self::PROTOCOL]['length']))
            return false;

		$publicUrl = $contextOptions[self::PROTOCOL]['publicUrl'];
		$authToken = $contextOptions[self::PROTOCOL]['authToken'];
        $id = $contextOptions[self::PROTOCOL]['id'];
        $part = $contextOptions[self::PROTOCOL]['part'];
        $sslVerifyPeer = $contextOptions[self::PROTOCOL]['sslVerifyPeer'];
        $sslVerifyPeerName = $contextOptions[self::PROTOCOL]['sslVerifyPeerName'];

        $this->position = 0;

		// STREAM OPENING LOGIC STARTS HERE
        if (!$id) { return null; }
        $url = $publicUrl . "/service/content/get?id=" . $id;
        if ($part != "") {
            $url = $url . "&part=" . $part;
        }

        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Content-type: application/x-www-form-urlencoded' . "\r\n" . 'Cookie: ' .  'ZM_AUTH_TOKEN=' . $authToken . "\r\n" ,
            ),
            'ssl' => array(
                'verify_peer' => $sslVerifyPeer,
                'verify_peer_name' => (!(0 == $sslVerifyPeerName))
            ),
        );
        $context = stream_context_create($opts);
        $this->zimbrahttpstream = fopen($url, 'rb', false, $context);

		$this->zimbrahttpstreamlength = $contextOptions[self::PROTOCOL]['length'];

		// STREAM OPENING LOGIC ENDS HERE
		// position should be zero, $this->zimbrahttpstream should be the http stream at the beginning of the data,  $this->zimbrahttpstreamlength should contain the correct length
			
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZimbraHttpStreamWrapper->stream_open(): initialized stream length: %d", $this->zimbrahttpstreamlength));

        return true;

    } catch (\Exception $e) {

        if ($options & STREAM_REPORT_ERRORS) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return false;
    }

    }

    /**
     * Reads from stream
     *
     * @param int $len      amount of bytes to be read
     *
     * @access public
     * @return string
     */
    public function stream_read($len) {
//        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZimbraHttpStreamWrapper->stream_read(): Reading bytes from stream length: %d", $len));
        $data = fread($this->zimbrahttpstream, $len);
        $this->position += strlen($data);
        return $data;
    }

    /**
     * Writes data to the stream.
     *
     * @param string $data
     * @return int
     */
    public function stream_write($data){
		// writing to the wrapper should not be possible
		return 0;
    }

    /**
     * Stream "seek" functionality.
     *
     * @param int $offset
     * @param int $whence
     * @return boolean
     */
    public function stream_seek($offset, $whence = SEEK_SET) {
//        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZimbraHttpStreamWrapper->stream_seek(): Looking for Offset: %d", $offset));
		// TODO if you implement SEEK it's not enough just to set the internal pointer, but the underlying stream also needs to be seeked to the correct position. 

        if ($whence == SEEK_CUR) {
            $this->position += $offset;
        }
        else if ($whence == SEEK_END) {
            $this->position = $this->zimbrahttpstreamlength + $offset;
        }
        else {
            $this->position = $offset;
        }
		
		// this is probably not possible with a http stream, if, it's enough to do:
		return fseek($this->zimbrahttpstream, $offset, $whence);
    }

    /**
     * Returns the current position on stream
     *
     * @access public
     * @return int
     */
    public function stream_tell() {
 //       ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZimbraHttpStreamWrapper->stream_tell(): At position: %d", $this->position));
        return $this->position;
    }

   /**
     * Indicates if 'end of file' is reached
     *
     * @access public
     * @return boolean
     */
    public function stream_eof() {
//        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZimbraHttpStreamWrapper->stream_eof(): Position: %d - EOF: %d", $this->position, $this->zimbrahttpstreamlength));
        return ($this->position >= $this->zimbrahttpstreamlength);
    }

    /**
    * Retrieves information about a stream
    *
    * @access public
    * @return array
    */
    public function stream_stat() {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZimbraHttpStreamWrapper->stream_stat(): Stat: %d", $this->zimbrahttpstreamlength));
        return array(
            7               => $this->zimbrahttpstreamlength,
            'size'          => $this->zimbrahttpstreamlength,
        );
    }

   /**
     * Instantiates a ZimbraHttpStreamWrapper
     *
     * @param string    $url               The url of the entry that should be streamed.
	 * @param string    $authtoken         The authtoken required to access the url.
	 * @param string    $id      	       The id .. 
	 * @param string    $part      	       The part ..
	 * @param long      $length   	       The length of the data ..
	 * @param int       $sslVerifyPeer     Flag to indicate if zimbra certificate should be validated 
	 * @param string    $sslVerifyPeerName Flag to indicate if the hostname in the zimbra certificate should be validated 
     *
     * @access public
     * @return ZimbraHttpStreamWrapper
     */
     static public function Open($authToken, $publicUrl, $id, $part, $length, $sslVerifyPeer = 0, $sslVerifyPeerName = 0) {

        $opts = array(
                self::PROTOCOL => array(
					'publicUrl' => $publicUrl,
					'authToken' => $authToken,
					'id' => $id,
					'part' => $part,
					'length' => $length,
					'sslVerifyPeer' => $sslVerifyPeer,
					'sslVerifyPeerName' => $sslVerifyPeerName,
				)
        );

        $context = stream_context_create($opts);
        return fopen(self::PROTOCOL . "://",'rb', false, $context);
    }
}

stream_wrapper_register(ZimbraHttpStreamWrapper::PROTOCOL, "ZimbraHttpStreamWrapper");

