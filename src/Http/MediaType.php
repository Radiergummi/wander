<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Http;

/**
 * Common media types based on IANA and MDN.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
 * @see     http://www.iana.org/assignments/media-types/media-types.xhtml
 *
 * @codeCoverageIgnore
 *
 * @package Radiergummi\Wander\Http
 * @author  Moritz Friedrich <m@9dev.de>
 * @license MIT
 */
final class MediaType
{

    /**
     * Electronic publication (EPUB) (.epub)
     */
    public const APPLICATION_EPUB = 'application/epub+zip';

    /**
     * GZip Compressed Archive (.gz)
     */
    public const APPLICATION_GZIP = 'application/gzip';

    /**
     * Java Archive (JAR) (.jar)
     */
    public const APPLICATION_JAVA_ARCHIVE = 'application/java-archive';

    /**
     * JSON format (.json)
     */
    public const APPLICATION_JSON = 'application/json';

    /**
     * JSON-LD format (.jsonld)
     */
    public const APPLICATION_LD_JSON = 'application/ld+json';

    /**
     * Microsoft Word (.doc)
     */
    public const APPLICATION_MSWORD = 'application/msword';

    /**
     * Any kind of binary data (.bin)
     */
    public const APPLICATION_OCTET_STREAM = 'application/octet-stream';

    /**
     * OGG (.ogx)
     */
    public const APPLICATION_OGG = 'application/ogg';

    /**
     * Adobe Portable Document Format (PDF) (.pdf)
     */
    public const APPLICATION_PDF = 'application/pdf';

    /**
     * Rich Text Format (RTF) (.rtf)
     */
    public const APPLICATION_RTF = 'application/rtf';

    /**
     * Amazon Kindle eBook format (.azw)
     */
    public const APPLICATION_VND_AMAZON_EBOOK = 'application/vnd.amazon.ebook';

    /**
     * Apple Installer Package (.mpkg)
     */
    public const APPLICATION_VND_APPLE_INSTALLER_XML = 'application/vnd.apple.installer+xml';

    /**
     * XUL (.xul)
     */
    public const APPLICATION_VND_MOZILLA_XUL_XML = 'application/vnd.mozilla.xul+xml';

    /**
     * Microsoft Excel (.xls)
     */
    public const APPLICATION_VND_MS_EXCEL = 'application/vnd.ms-excel';

    /**
     * MS Embedded OpenType fonts (.eot)
     */
    public const APPLICATION_VND_MS_FONTOBJECT = 'application/vnd.ms-fontobject';

    /**
     * Microsoft PowerPoint (.ppt)
     */
    public const APPLICATION_VND_MS_POWERPOINT = 'application/vnd.ms-powerpoint';

    /**
     * OpenDocument presentation document (.odp)
     */
    public const APPLICATION_VND_OASIS_OPENDOCUMENT_PRESENTATION = 'application/vnd.oasis.opendocument.presentation';

    /**
     * OpenDocument spreadsheet document (.ods)
     */
    public const APPLICATION_VND_OASIS_OPENDOCUMENT_SPREADSHEET = 'application/vnd.oasis.opendocument.spreadsheet';

    /**
     * OpenDocument text document (.odt)
     */
    public const APPLICATION_VND_OASIS_OPENDOCUMENT_TEXT = 'application/vnd.oasis.opendocument.text';

    /**
     * Microsoft PowerPoint (OpenXML) (.pptx)
     */
    public const APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_PRESENTATION = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

    /**
     * Microsoft Excel (OpenXML) (.xlsx)
     */
    public const APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_SHEET = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /**
     * Microsoft Word (OpenXML) (.docx)
     */
    public const APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_DOCUMENT = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    /**
     * RAR archive (.rar)
     */
    public const APPLICATION_VND_RAR = 'application/vnd.rar';

    /**
     * Microsoft Visio (.vsd)
     */
    public const APPLICATION_VND_VISIO = 'application/vnd.visio';

    /**
     * XHTML (.xhtml)
     */
    public const APPLICATION_XHTML_XML = 'application/xhtml+xml';

    /**
     * XML (.xml)
     * If not readable from casual users (RFC 3023, section 3)
     *
     * @see https://tools.ietf.org/html/rfc3023#section-3
     */
    public const APPLICATION_XML = 'application/xml';

    /**
     * 7-zip archive (.7z)
     */
    public const APPLICATION_X_7Z_COMPRESSED = 'application/x-7z-compressed';

    /**
     * AbiWord document (.abw)
     */
    public const APPLICATION_X_ABIWORD = 'application/x-abiword';

    /**
     * BZip archive (.bz)
     */
    public const APPLICATION_X_BZIP = 'application/x-bzip';

    /**
     * BZip2 archive (.bz2)
     */
    public const APPLICATION_X_BZIP2 = 'application/x-bzip2';

    /**
     * C-Shell script (.csh)
     */
    public const APPLICATION_X_CSH = 'application/x-csh';

    /**
     * Archive document (multiple files embedded) (.arc)
     */
    public const APPLICATION_X_FREEARC = 'application/x-freearc';

    /**
     * Hypertext Preprocessor (Personal Home Page) (.php)
     */
    public const APPLICATION_X_HTTPD_PHP = 'application/x-httpd-php';

    /**
     * Bourne shell script (.sh)
     */
    public const APPLICATION_X_SH = 'application/x-sh';

    /**
     * Small web format (SWF) or Adobe Flash document (.swf)
     */
    public const APPLICATION_X_SHOCKWAVE_FLASH = 'application/x-shockwave-flash';

    /**
     * Tape Archive (TAR) (.tar)
     */
    public const APPLICATION_X_TAR = 'application/x-tar';

    /**
     * URL-encoded data
     */
    public const APPLICATION_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     * ZIP archive (.zip)
     */
    public const APPLICATION_ZIP = 'application/zip';

    /**
     * 3GPP audio container (.3gp)
     */
    public const AUDIO_3GPP = 'audio/3gpp';

    /**
     * 3GPP2 audio container (.3g2)
     */
    public const AUDIO_3GPP2 = 'audio/3gpp2';

    /**
     * AAC audio (.aac)
     */
    public const AUDIO_AAC = 'audio/aac';

    /**
     * Musical Instrument Digital Interface (MIDI) (.midi, .mid)
     */
    public const AUDIO_MIDI = 'audio/midi';

    /**
     * MP3 audio (.mp3)
     */
    public const AUDIO_MPEG = 'audio/mpeg';

    /**
     * OGG audio (.oga)
     */
    public const AUDIO_OGG = 'audio/ogg';

    /**
     * Opus audio (.opus)
     */
    public const AUDIO_OPUS = 'audio/opus';

    /**
     * Waveform Audio Format (.wav)
     */
    public const AUDIO_WAV = 'audio/wav';

    /**
     * WEBM audio (.weba)
     */
    public const AUDIO_WEBM = 'audio/webm';

    /**
     * OpenType font (.otf)
     */
    public const FONT_OTF = 'font/otf';

    /**
     * TrueType Font (.ttf)
     */
    public const FONT_TTF = 'font/ttf';

    /**
     * Web Open Font Format (WOFF) (.woff)
     */
    public const FONT_WOFF = 'font/woff';

    /**
     * Web Open Font Format (WOFF) (.woff2)
     */
    public const FONT_WOFF2 = 'font/woff2';

    /**
     * Windows OS/2 Bitmap Graphics (.bmp)
     */
    public const IMAGE_BMP = 'image/bmp';

    /**
     * Graphics Interchange Format (GIF) (.gif)
     */
    public const IMAGE_GIF = 'image/gif';

    /**
     * JPEG images (.jpg, .jpeg)
     */
    public const IMAGE_JPEG = 'image/jpeg';

    /**
     * Portable Network Graphics (.png)
     */
    public const IMAGE_PNG = 'image/png';

    /**
     * Scalable Vector Graphics (SVG) (.svg)
     */
    public const IMAGE_SVG_XML = 'image/svg+xml';

    /**
     * Tagged Image File Format (TIFF) (.tiff, .tif)
     */
    public const IMAGE_TIFF = 'image/tiff';

    /**
     * Icon format (.ico)
     */
    public const IMAGE_VND_MICROSOFT_ICON = 'image/vnd.microsoft.icon';

    /**
     * WEBP image (.webp)
     */
    public const IMAGE_WEBP = 'image/webp';

    /**
     * iCalendar format (.ics)
     */
    public const TEXT_CALENDAR = 'text/calendar';

    /**
     * Cascading Style Sheets (CSS) (.css)
     */
    public const TEXT_CSS = 'text/css';

    /**
     * Comma-separated values (CSV) (.csv)
     */
    public const TEXT_CSV = 'text/csv';

    /**
     * HyperText Markup Language (HTML) (.html, .htm)
     */
    public const TEXT_HTML = 'text/html';

    /**
     * JavaScript (.js, .mjs)
     *
     * Per the following specifications:
     *
     * @see https://html.spec.whatwg.org/multipage/#scriptingLanguages
     * @see https://html.spec.whatwg.org/multipage/#dependencies:willful-violation
     * @see https://datatracker.ietf.org/doc/draft-ietf-dispatch-javascript-mjs/
     */
    public const TEXT_JAVASCRIPT = 'text/javascript';

    /**
     * Text, (generally ASCII or ISO 8859-n) (.txt)
     */
    public const TEXT_PLAIN = 'text/plain';

    /**
     * XML (.xml)
     * If readable from casual users (RFC 3023, section 3)
     *
     * @see https://tools.ietf.org/html/rfc3023#section-3
     */
    public const TEXT_XML = 'text/xml';

    /**
     * 3GPP video container (.3gp)
     */
    public const VIDEO_3GPP = 'video/3gpp';

    /**
     * 3GPP2 video container (.3g2)
     */
    public const VIDEO_3GPP2 = 'video/3gpp2';

    /**
     * MPEG transport stream (.ts)
     */
    public const VIDEO_MP2T = 'video/mp2t';

    /**
     * MPEG Video (.mpeg)
     */
    public const VIDEO_MPEG = 'video/mpeg';

    /**
     * OGG video (.ogv)
     */
    public const VIDEO_OGG = 'video/ogg';

    /**
     * WEBM video (.webm)
     */
    public const VIDEO_WEBM = 'video/webm';

    /**
     * AVI: Audio Video Interleave (.avi)
     */
    public const VIDEO_X_MSVIDEO = 'video/x-msvideo';
}
