<?php
namespace Application\MiddleWare;

use Exception;
use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Value object representing a file uploaded through an HTTP request.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 */
class UploadedFile implements UploadedFileInterface
{

    protected $field;   // original name of file upload field
    protected $info;    // $_FILES[$field]
    protected $stream;
    protected $randomize;
    protected $movedName = '';

    /**
     *
     * @param string $field = name of file upload field
     * @param array $info = array from $_FILES[$field]
     * @param boolean $randomize = TRUE if you want a new random filename
     *
     */
    public function __construct($field, array $info, $randomize = FALSE)
    {
        $this->info = $info;
        $this->field = $field;
        $this->randomize = $randomize;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        if (!$this->stream) {
            if ($this->movedName) {
                $this->stream = new Stream($this->movedName);
            } else {
                $this->stream = new Stream($info['tmp_name']);
            }
        }
        return $this->stream;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \InvalidArgumentException if the $path specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if ($this->moved) {
            throw new Exception(Constants::ERROR_MOVE_DONE);
        }
        if (!file_exists($targetPath)) {
            throw new InvalidArgumentException(Constants::ERROR_BAD_DIR);
        }
        $tempFile = $this->info['tmp_name'] ?? FALSE;
        if (!$tempFile || !file_exists($tempFile)) {
            throw new Exception(Constants::ERROR_BAD_FILE);
        }
        if (!is_uploaded_file($tempFile)) {
            throw new Exception(Constants::ERROR_FILE_NOT);
        }
        if ($this->randomize) {
            $final = bin2hex(random_bytes(8)) . '.txt';
        } else {
            $final = $this->info['name'];
        }
        $final = $targetPath . '/' . $final;
        $final = str_replace('//', '/', $final);
        if (!move_uploaded_file($tempFile, $final)) {
            throw new RuntimeException(Constants::ERROR_MOVE_UNABLE);
        }
        $this->movedName = $final;
        return TRUE;
    }

    public function getMovedName()
    {
        return $this->movedName ?? NULL;
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->info['size'] ?? NULL;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        if ($this->movedName) {
            return UPLOAD_ERR_OK;
        }
        return $this->info['error'];
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->info['name'] ?? NULL;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->info['type'] ?? NULL;
    }

}
