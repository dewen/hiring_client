<?php

/**
 * REST client controller
 *
 */
class Control
{
    /**
     * input file handler
     *
     * @var $_handler       resource handler
     */
    protected $_handler = null;

    /**
     * start the process
     *
     * @param   $params     user's input from command line
     * @return  void
     */ 
    public function run($params)
    {
        if (!isset($params[1])) 
            throw new Exception('Input file missing. ');

        $this->loadInput($params[1]);
        $this->process($this->getVersion());
    }

    /**
     * load the input file with filename
     *
     * @param   $filename       input file name
     * @return  void
     */ 
    public function loadInput($filename)
    {
        if (!file_exists($filename)) 
            throw new Exception('Failed to find file ['.$filename.']. Make sure to include path to file.');

        if (!$this->_handler)
            $this->_handler = fopen($filename, "r");

    }

    /**
     * get the request API version
     *
     * @return  string      version number
     */ 
    public function getVersion()
    {
        $input = fgets($this->_handler);
        rewind($this->_handler);
        return (strpos(trim($input), 'auth') === 0) ? 'v2' : 'v1';
    }

    /**
     * take each command line from input and process the request. Output the 
     * result to stdout.
     *
     * @param   string      API version
     *
     * @return  void
     */ 
    public function process($version)
    {
        $client = new Client(HIRING_API_URL, $version);

        while($input = $this->getInput()) {
            $result = '';
            switch($input['cmd']) {
                case 'get':
                    $result = $client->get($input['key']);
                    break;
                case 'set':
                    $result = $client->set($input['key'], $input['value']);
                    break;
                case 'delete':
                    $result = $client->delete($input['key']);
                    break;
                case 'auth':
                    $result = $client->auth($input['user'], $input['pass']);
                    break;
                case 'list':
                    $result = $client->get();
                    break;
            }
            echo $result . PHP_EOL;
        }
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        if ($this->_handler)
            fclose($this->_handler);
    }

    public function getInput()
    {
        $line = trim(fgets($this->_handler));
        
        if (!$line) return false;

        $params = explode(' ', $line);

        $cmd = $params[0];
        
        $input = array(
            'cmd' => $cmd
        );

        switch($cmd) {
            case 'get':
                $input['key'] = $params[1];
                break;
            case 'set':
                $input['key'] = $params[1];
                $input['value'] = $params[2];
                break;
            case 'list':
                break;
            case 'delete':
                $input['key'] = $params[1];
                break;
            case 'auth':
                $input['user'] = $params[1];
                $input['pass'] = $params[2];
                break;
            default:
                throw new Exception('Command['.$cmd.'] is not supported');
        }

        return $input;
    }
}
