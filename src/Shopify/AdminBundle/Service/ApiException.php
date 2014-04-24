	
class ApiException extends \Exception
{	
	/**
	* Cosntructor
	*
	* @return void
	* @author James Pudney james@phpgenie.co.uk
	**/
	public function __construct($message, $code = 0)
	{
	if ( is_array($message) )
	{
	$message = json_encode($message);
	}

	parent::__construct($message, $code);
	}
	}