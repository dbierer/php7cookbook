<?php
namespace Application\Database\Mapper;

use InvalidArgumentException;

class FieldConfig
{
	const ERROR_SOURCE = 'ERROR: need to specify either or both destTable and source';
	const ERROR_DEST   = 'ERROR: need to specify either both destTable and destCol or neither';

	public $key;
    public $source;
    public $destTable;
    public $destCol;
    public $default;
    // NOTE: default == scalar value | callback
    public function __construct($source = NULL,
                                $destTable = NULL,
                                $destCol   = NULL,
                                $default   = NULL)
    {
		// generate key from source + destTable + destCol
		$this->key = $source . '.' . $destTable . '.' . $destCol;
        $this->source = $source;
        $this->destTable = $destTable;
        $this->destCol = $destCol;
        $this->default = $default;
        if (($destTable && !$destCol) || (!$destTable && $destCol)) {
			throw new InvalidArgumentException(self::ERROR_DEST);
		}
		if (!$destTable && !$source) {
			throw new InvalidArgumentException(self::ERROR_SOURCE);
		}
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getDestTable()
    {
        return $this->destTable;
    }

    public function getDestCol()
    {
        return $this->destCol;
    }

    public function getDefault($row = array())
    {
		if (is_callable($this->default)) {
			return call_user_func($this->default, $row);
		} else {
			return $this->default;
		}
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function setDestTable($destTable)
    {
        $this->destTable = $destTable;
    }

    public function setDestCol($destCol)
    {
        $this->destCol = $destCol;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

}
