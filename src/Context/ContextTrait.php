<?php

namespace Radiergummi\Wander\Context;

use Psr\Http\Message\MessageInterface;
use Radiergummi\Wander\Http\Header;

use function strtok;
use function trim;

trait ContextTrait
{

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given
     *                  header. If the header does not appear in the message,
     *                  this method MUST return an empty array.
     */
    public function getHeader(string $name): array
    {
        return $this->getMessage()->getHeader($name);
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using comma
     * concatenation. For such headers, use getHeader() instead and supply your
     * own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty string.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header
     *                concatenated together using a comma. If the header does
     *                not appear in the message, this method MUST return an
     *                empty string.
     */
    public function getHeaderLine(string $name): string
    {
        return $this->getMessage()->getHeaderLine($name);
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers.
     *                    Each key MUST be a header name, and each value MUST be
     *                    an array of strings for that header.
     */
    public function getHeaders(): array
    {
        return $this->getMessage()->getHeaders();
    }

    /**
     * Retrieves the current content type.
     * If none is set, null will be returned.
     *
     * @param bool $omitEncoding Whether to omit any eventual encoding added to
     *                           the content type
     *
     * @return string|null
     */
    public function getContentType(bool $omitEncoding = false): ?string
    {
        $mediaType = $this->getHeaderLine(Header::CONTENT_TYPE) ?: null;

        if ( ! $mediaType) {
            return null;
        }

        if ( ! $omitEncoding) {
            return $mediaType;
        }

        return trim(strtok($mediaType, ';'));
    }

    /**
     * Retrieves the HTTP message this context refers to
     *
     * @return MessageInterface
     */
    abstract protected function getMessage(): MessageInterface;
}
