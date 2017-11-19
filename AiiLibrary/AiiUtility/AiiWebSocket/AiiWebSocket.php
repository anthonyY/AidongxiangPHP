<?php
namespace AiiUtility\AiiWebSocket;

class AiiWebSocket
{

    const VERSION = '0.1.0';

    const TOKEN_LENGHT = 16;

    const DEBUG = false;

    private $_socket;

    private $_check = true;

    private $_address;

    private $_port;

    private $_key;

    private $_path = '/';

    private $_origin = null;

    function __construct()
    {
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (false === $this->_socket)
        {
            echo "服务器不支持socket" . PHP_EOL;
            return $this->_check = false;
        }
    }

    /**
     * 还没知道返回的内容是什么，但是有了这个可以防止请求过快的问题
     * 待优化，没解决阻塞问题，没解决超过长度问题
     *
     * @return string
     * @version 2016-2-25 WZ
     */
    public function read()
    {
        // 感觉应该用socket_recv但是不返回，而且提出警告
        return socket_read($this->_socket, 8192, PHP_BINARY_READ);
    }

    /**
     * 发送信息到服务器
     *
     * @param string $content            
     * @return boolean|string
     * @version 2016-2-25 WZ
     */
    public function send($content)
    {
        if (! $this->_check) {
            return false;
        }
        if (self::DEBUG)
        {
            echo '发送：' . $content . PHP_EOL;
        }
        
        $content = $this->_hybi10Encode($content); // 发送的内容需要转换的
        if (false === socket_write($this->_socket, $content, strlen($content)))
        {
            echo "发送失败，原因： " . iconv("GB2312", "UTF-8//TRANSLIT", socket_strerror(socket_last_error($this->_socket))) . PHP_EOL;
            return false;
        }
        $return = '';
        $return = $this->read(); // 最好服务器有返回，不然请求太快可能会导致失败
        return $return;
    }

    /**
     * 测试结果
     *
     * @version 2016-3-3 WZ
     */
    private function testSent()
    {
        // 大于6066个字符崩溃
        $content = '';
        for ($i = 0; $i < (4096 + 1024 + 512 + 256 + 128 + 32 + 16 + 2); $i ++)
        {
            $content .= $i % 10;
        }
        $this->send($content);
    }

    /**
     * 复制别人写好的转码方法，貌似只支持到6066个字符
     *
     * @param string $payload
     *            内容
     * @param string $type
     *            text,close,ping,pong
     * @param string $masked            
     * @return boolean|Ambigous boolean, unknown>
     * @version 2016-3-3 WZ
     */
    private function _hybi10Encode($payload, $type = 'text', $masked = false)
    {
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);
        
        switch ($type)
        {
            case 'text':
                
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;
            case 'close':
                
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;
            case 'ping':
                
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;
            case 'pong':
                
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }
        
        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535)
        {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i ++)
            {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127)
            {
                $this->close(1004);
                return false;
            }
        }
        elseif ($payloadLength > 125)
        {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        }
        else
        {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }
        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i)
        {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true)
        {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i ++)
            {
                $mask[$i] = chr(rand(0, 255));
            }
            
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        // append payload to frame:
        $framePayload = array();
        for ($i = 0; $i < $payloadLength; $i ++)
        {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }

    /**
     * 连接、握手
     *
     * @param string $address            
     * @param string $port            
     * @return boolean
     * @version 2016-3-3 WZ
     */
    function connect($address = 'localhost', $port = '9501')
    {
        if ($address && $port)
        {
            // 连接
            $this->_address = $address;
            $this->_port = $port;
            try {
                if (false === @socket_connect($this->_socket, $address, $port))
                {
                    throw new \Exception("$address:$port 链接失败");
                }
            } catch (Exception $e) {
                echo $e->__toString();
                return $this->_check = false;
            }
        }
        $this->_key = $this->generateToken(self::TOKEN_LENGHT);
        
        // 握手
        $header = $this->createHeader();
        socket_write($this->_socket, $header, strlen($header));
        $read = $this->read(); // 可以获取握手时服务器的信息，需要获取，不然两次快速的请求会导致发送失败
    }

    /**
     * Create header for websocket client
     *
     * @return string
     */
    private function createHeader()
    {
        $host = $this->_address;
        if ($host === '127.0.0.1' || $host === '0.0.0.0')
        {
            $host = 'localhost';
        }
        return "GET {$this->_path} HTTP/1.1" . "\r\n" . "Origin: {$this->_origin}" . "\r\n" . "Host: {$host}:{$this->_port}" . "\r\n" . "Sec-WebSocket-Key: {$this->_key}" . "\r\n" . "User-Agent: PHPWebSocketClient/" . self::VERSION . "\r\n" . "Upgrade: websocket" . "\r\n" . "Connection: Upgrade" . "\r\n" . "Sec-WebSocket-Protocol: wamp" . "\r\n" . "Sec-WebSocket-Version: 13" . "\r\n" . "\r\n";
    }

    /**
     * Generate token
     *
     * @param int $length            
     *
     * @return string
     */
    private function generateToken($length)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
        $useChars = array();
        // select some random chars:
        for ($i = 0; $i < $length; $i ++)
        {
            $useChars[] = $characters[mt_rand(0, strlen($characters) - 1)];
        }
        // Add numbers
        array_push($useChars, rand(0, 9), rand(0, 9), rand(0, 9));
        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, self::TOKEN_LENGHT);
        return base64_encode($randomString);
    }

    function __destruct()
    {
        if ($this->_socket)
        {
            socket_close($this->_socket);
        }
    }
}
