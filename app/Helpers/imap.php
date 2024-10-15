<?php

/**
 * imap-attachment.php
 *
 * @author hakre <hakre.wordpress.com>
 * @link http://stackoverflow.com/questions/9974334/how-to-download-mails-attachment-to-a-specific-folder-using-imap-and-php
 */

/**
 * Utility Class
 */

namespace App\Controllers;

use Exception;

class IMAP
{
    /**
     *
     * =?x-unknown?B?
     * =?iso-8859-1?Q?
     * =?windows-1252?B?
     *
     * @param string $stringQP
     * @param string $base (optional) charset (IANA, lowercase)
     * @return string UTF-8
     */
    public static function decodeToUTF8($stringQP, $base = 'windows-1252')
    {
        $pairs = array(
            '?x-unknown?' => "?$base?"
        );
        $stringQP = strtr($stringQP, $pairs);
        return imap_utf8($stringQP);
    }
}

class IMAPMailbox implements IteratorAggregate, Countable
{
    private $stream;

    public function __construct($hostname, $username, $password)
    {
        $stream = imap_open($hostname, $username, $password);
        if (FALSE === $stream) {
            throw new Exception('Connect failed: ' . imap_last_error());
        }
        $this->stream = $stream;
    }

    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @return stdClass
     */
    public function check()
    {
        $info = imap_check($this->stream);
        if (FALSE === $info) {
            throw new Exception('Check failed: ' . imap_last_error());
        }
        return $info;
    }

    /**
     * @param string $criteria
     * @param int $options
     * @param int $charset
     * @return IMAPMessage[]
     * @throws Exception
     */
    public function search($criteria, $options = NULL, $charset = NULL)
    {
        $emails = imap_search($this->stream, $criteria, $options, $charset);
        if (FALSE === $emails) {
            throw new Exception('Search failed: ' . imap_last_error());
        }
        foreach ($emails as &$email) {
            $email = $this->getMessageByNumber($email);
        }
        return $emails;
    }

    /**
     * @param int $number
     * @return IMAPMessage
     */
    public function getMessageByNumber($number)
    {
        return new IMAPMessage($this, $number);
    }

    public function getOverview($sequence = NULL)
    {
        if (NULL === $sequence) {
            $sequence = sprintf('1:%d', count($this));
        }
        return new IMAPOverview($this, $sequence);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing Iterator or
     * Traversable
     */
    public function getIterator()
    {
        return $this->getOverview()->getIterator();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->check()->Nmsgs;
    }
}

class IMAPOverview extends ArrayObject
{
    private $mailbox;

    public function __construct(IMAPMailbox $mailbox, $sequence)
    {
        $result = imap_fetch_overview($mailbox->getStream(), $sequence);
        if (FALSE === $result) {
            throw new Exception('Overview failed: ' . imap_last_error());
        }
        $this->mailbox = $mailbox;
        foreach ($result as $overview) {
            if (!isset($overview->subject)) {
                $overview->subject = '';
            } else {
                $overview->subject = IMAP::decodeToUTF8($overview->subject);
            }
        }
        parent::__construct($result);
    }

    /**
     * @return IMAPMailbox
     */
    public function getMailbox()
    {
        return $this->mailbox;
    }
}

class IMAPMessage
{
    private $mailbox;
    private $number;
    private $stream;

    public function __construct(IMAPMailbox $mailbox, $number)
    {
        $this->mailbox = $mailbox;
        $this->number = $number;
        $this->stream = $mailbox->getStream();
    }

    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     * @return string
     */
    public function fetchBody($number)
    {
        return imap_fetchbody($this->stream, $this->number, $number);
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    public function fetchOverview()
    {
        $result = imap_fetch_overview($this->stream, $this->number);
        if (FALSE === $result) {
            throw new Exception('FetchOverview failed: ' . imap_last_error());
        }
        list($result) = $result;
        foreach ($result as &$prop) {
            $prop = imap_utf8($prop);
        }
        return $result;
    }

    public function fetchStructure()
    {
        $structure = imap_fetchstructure($this->stream, $this->number);
        if (FALSE === $structure) {
            throw new Exception('FetchStructure failed: ' . imap_last_error());
        }
        return $structure;
    }

    /**
     * @return IMAPAttachments
     */
    public function getAttachments()
    {
        return new IMAPAttachments($this);
    }

    public function __toString()
    {
        return (string)$this->number;
    }
}

class IMAPAttachment
{
    private $attachment;
    private $message;

    public function __construct(IMAPMessage $message, $attachment)
    {
        $this->message = $message;
        $this->attachment = $attachment;
    }

    /**
     * @return string;
     */
    public function getBody()
    {
        return $this->message->fetchBody($this->attachment->number);
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return (int)$this->attachment->bytes;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->getFilename(), PATHINFO_EXTENSION);
    }

    public function getFilename()
    {
        $filename = $this->attachment->filename;
        NULL === $filename && $filename = $this->attachment->name;
        return $filename;
    }

    public function __toString()
    {
        $encoding = $this->attachment->encoding;
        switch ($encoding) {
            case 0: // 7BIT
            case 1: // 8BIT
            case 2: // BINARY
                return $this->getBody();

            case 3: // BASE-64
                return base64_decode($this->getBody());

            case 4: // QUOTED-PRINTABLE
                return imap_qprint($this->getBody());
        }
        throw new Exception(sprintf('Encoding failed: Unknown encoding %s (5: OTHER).', $encoding));
    }
}

class IMAPAttachments extends ArrayObject
{
    private $message;

    public function __construct(IMAPMessage $message)
    {
        $array = $this->setMessage($message);
        parent::__construct($array);
    }

    private function setMessage(IMAPMessage $message)
    {
        $this->message = $message;
        return $this->parseStructure($message->fetchStructure());
    }

    private function parseStructure($structure)
    {
        $attachments = array();
        if (!isset($structure->parts)) {
            return $attachments;
        }

        foreach ($structure->parts as $index => $part) {
            if (!$part->ifdisposition) continue;
            $attachment = new stdClass;
            $attachment->isAttachment = FALSE;
            $attachment->number = $index + 1;
            $attachment->bytes = $part->bytes;
            $attachment->encoding = $part->encoding;
            $attachment->filename = NULL;
            $attachment->name = NULL;
            $part->ifdparameters
                && ($attachment->filename = $this->getAttribute($part->dparameters, 'filename'))
                && $attachment->isAttachment = TRUE;
            $part->ifparameters
                && ($attachment->name = $this->getAttribute($part->parameters, 'name'))
                && $attachment->isAttachment = TRUE;

            $attachment->isAttachment
                && $attachments[] = new IMAPAttachment($this->message, $attachment);
        }
        return $attachments;
    }

    private function getAttribute($params, $name)
    {
        foreach ($params as $object) {
            if ($object->attribute == $name) {
                return IMAP::decodeToUTF8($object->value);
            }
        }
        return NULL;
    }
}
