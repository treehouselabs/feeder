<?php

namespace TreeHouse\Feeder\Transport;

use TreeHouse\Feeder\Event\FetchProgressEvent;
use TreeHouse\Feeder\Exception\TransportException;
use TreeHouse\Feeder\FeedEvents;
use TreeHouse\Feeder\Transport\Matcher\FileMatcher;
use TreeHouse\Feeder\Transport\Matcher\GlobMatcher;
use TreeHouse\Feeder\Transport\Matcher\MatcherInterface;
use TreeHouse\Feeder\Transport\Matcher\PatternMatcher;

class FtpTransport extends AbstractTransport implements ProgressAwareInterface
{
    /**
     * @var resource
     */
    protected $ftpConnection;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var MatcherInterface
     */
    protected $fileMatcher;

    /**
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $file
     * @param array  $options
     *
     * @return FtpTransport
     */
    public static function create($host, $user = null, $pass = null, $file, array $options = [])
    {
        $conn = new Connection(array_merge(
            [
                'host' => $host,
                'user' => $user,
                'pass' => $pass,
                'file' => $file,
                'timeout' => 10,
            ],
            $options
        ));
        $transport = new self($conn);

        return $transport;
    }

    public function __clone()
    {
        parent::__clone();

        $this->closeFtpConnection();
        $this->fileName = null;
        $this->fileMatcher = null;
    }

    public function __destruct()
    {
        $this->closeFtpConnection();
    }

    public function __toString()
    {
        if ($this->fileName) {
            $file = $this->fileName;
        } else {
            $file = $this->connection['file'];
        }

        return $this->connection['host'] . ':/' . $file;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->connection['host'];
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return isset($this->connection['user']) ? $this->connection['user'] : null;
    }

    /**
     * @return string|null
     */
    public function getPass()
    {
        return isset($this->connection['pass']) ? $this->connection['pass'] : null;
    }

    /**
     * @return string|null
     */
    public function getMode()
    {
        return isset($this->connection['mode']) ? $this->connection['mode'] : null;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->connection['mode'] = $mode;
    }

    /**
     * @return bool|null
     */
    public function getPasv()
    {
        return isset($this->connection['pasv']) ? (boolean) $this->connection['pasv'] : null;
    }

    /**
     * @param bool $pasv
     */
    public function setPasv($pasv)
    {
        $this->connection['pasv'] = (boolean) $pasv;
    }

    /**
     * @return bool|null
     */
    public function getPattern()
    {
        return isset($this->connection['pattern']) ? (boolean) $this->connection['pattern'] : null;
    }

    /**
     * @param bool $pattern
     */
    public function setPattern($pattern)
    {
        $this->connection['pattern'] = (boolean) $pattern;
    }

    /**
     * @param string $file
     */
    public function setFilename($file)
    {
        $this->connection['file'] = $file;
        $this->fileName = null;
    }

    /**
     * Returns the file to download from the ftp. Handles globbing rules and
     * checks if the file is listed in the remote dir.
     *
     * @throws TransportException When remote file could not be found
     * @return string
     *
     */
    public function getFilename()
    {
        if (!$this->fileName) {
            $matcher = $this->getFileMatcher();
            $this->fileName = $this->searchFile($matcher);
        }

        return $this->fileName;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastModifiedDate()
    {
        // see if uploaded feed is newer
        if ($time = ftp_mdtm($this->getFtpConnection(), $this->getFilename())) {
            return new \DateTime('@' . $time);
        }
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return ftp_size($this->getFtpConnection(), $this->getFilename());
    }

    /**
     * @param MatcherInterface $matcher
     */
    public function setFileMatcher(MatcherInterface $matcher)
    {
        $this->fileMatcher = $matcher;
    }

    /**
     * @return MatcherInterface
     */
    public function getFileMatcher()
    {
        if (null === $this->fileMatcher) {
            $this->fileMatcher = $this->createFileMatcher();
        }

        return $this->fileMatcher;
    }

    /**
     * @param MatcherInterface $matcher
     *
     * @throws TransportException
     *
     * @return string
     */
    protected function searchFile(MatcherInterface $matcher)
    {
        $conn = $this->getFtpConnection();
        $cwd = ftp_pwd($conn);
        $files = ftp_nlist($conn, $cwd);

        if (false === $files) {
            $msg = sprintf('Error listing files from directory "%s"', $cwd);
            if (!$this->getPasv()) {
                $msg .= '. You might want to try passive mode using "pasv: true" in your transport configuration.';
            }

            throw new TransportException($msg);
        }

        // strip cwd off the files
        $files = array_map(function ($file) use ($cwd) {
            return preg_replace(sprintf('/^%s/', preg_quote($cwd, '/')), '', $file);
        }, $files);

        if (null !== $file = $matcher->match($files)) {
            return $file;
        }

        throw new TransportException(sprintf('File "%s" was not found on FTP', (string) $matcher));
    }

    /**
     * @param string $destination
     */
    protected function doFetch($destination)
    {
        $tmpFile = $this->downloadToTmpFile();

        // download complete, move to actual destination
        rename($tmpFile, $destination);
    }

    /**
     * @throws TransportException
     * @return string
     *
     */
    protected function downloadToTmpFile()
    {
        $conn = $this->getFtpConnection();
        $file = $this->getFilename();
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($file);

        $fileSize = $this->getSize();

        $mode = $this->getMode() ? constant('FTP_' . strtoupper($this->getMode())) : FTP_ASCII;

        $ret = ftp_nb_get($conn, $tmpFile, $file, $mode);
        $currentBytes = 0;
        while ($ret === FTP_MOREDATA) {
            $ret = ftp_nb_continue($conn);
            clearstatcache();
            $bytes = filesize($tmpFile);
            $diff = $bytes - $currentBytes;
            $currentBytes = $bytes;

            $this->eventDispatcher->dispatch(
                FeedEvents::FETCH_PROGRESS,
                new FetchProgressEvent($currentBytes, $diff, $fileSize)
            );
        }

        if ($ret !== FTP_FINISHED) {
            throw new TransportException(sprintf('Error downloading feed to %s', $tmpFile));
        }

        return $tmpFile;
    }

    /**
     * Returns shared ftp connection.
     *
     * @return resource
     */
    protected function getFtpConnection()
    {
        if (is_null($this->ftpConnection)) {
            $host = $this->connection['host'];
            $user = $this->connection['user'];
            $pass = $this->connection['pass'];

            $this->ftpConnection = $this->connect($host, $user, $pass);

            // set timeout
            ftp_set_option($this->ftpConnection, FTP_TIMEOUT_SEC, $this->connection['timeout']);

            // set passive mode if it's defined
            if (null !== $pasv = $this->getPasv()) {
                ftp_pasv($this->ftpConnection, $pasv);
            }
        }

        return $this->ftpConnection;
    }

    /**
     * Connects to ftp.
     *
     * @param string $host
     * @param string $user
     * @param string $pass
     *
     * @throws TransportException
     * @return resource
     *
     */
    protected function connect($host, $user, $pass)
    {
        $conn = ftp_connect($host);
        if (($conn === false) || (@ftp_login($conn, $user, $pass) === false)) {
            throw new TransportException(
                is_resource($conn) ? 'Could not login to FTP' : 'Could not make FTP connection'
            );
        }

        return $conn;
    }

    /**
     * Closes shared ftp connection.
     */
    protected function closeFtpConnection()
    {
        if (is_resource($this->ftpConnection)) {
            ftp_close($this->ftpConnection);
        }

        $this->ftpConnection = null;
    }

    /**
     * @return MatcherInterface
     */
    protected function createFileMatcher()
    {
        $file = $this->connection['file'];

        // see if a pattern is used
        if ($pattern = $this->getPattern()) {
            return new PatternMatcher($file);
        }

        // see if globbing is used
        if (false !== strpos($file, '*')) {
            return new GlobMatcher($file);
        }

        // just a regular file matcher
        return new FileMatcher($file);
    }
}
